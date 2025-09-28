<?php

namespace App\Http\Controllers;

use App\Account;
use App\ClientPayment;
use App\User;
use App\Config;
use App\Invoice;
use App\Mikrotik;
use App\Plan;
use App\Reseller;
use App\Client;
use App\SMS\DeelkoSMS;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Controllers\Admin\ClientController; // Import the ClientController

class BkashWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Parse raw body content
        $rawBody = $request->getContent();
        $payload = json_decode($rawBody, true);

        // Simplify payload to get only required fields
        $simplifiedPayload = [
            'Type' => $payload['Type'] ?? null,
            'Message' => isset($payload['Message']) ? json_decode($payload['Message'], true) : null
        ];

        // Log simplified payload
        Log::info('Simplified payload:', $simplifiedPayload ?: []);

        $message = $simplifiedPayload['Message'];

        if ($message) {
             // Use firstOrCreate to prevent duplicate entries based on trxID
        $transaction = DB::table('bkash_webhook_transactions')->where('trxID', $message['trxID'])->first();
            // Insert webhook transaction into database
            if (!$transaction) {
            DB::table('bkash_webhook_transactions')->insert([
                'type' => $simplifiedPayload['Type'],
                'debitMSISDN' => $message['debitMSISDN'] ?? null,
                'creditOrganizationName' => $message['creditOrganizationName'] ?? null,
                'creditShortCode' => $message['creditShortCode'] ?? null,
                'trxID' => $message['trxID'] ?? null,
                'transactionStatus' => $message['transactionStatus'] ?? null,
                'transactionReference' => $message['transactionReference'] ?? null,
                'transactionType' => $message['transactionType'] ?? null,
                'amount' => $message['amount'] ?? null,
                'currency' => $message['currency'] ?? null,
                'dateTime' => isset($message['dateTime']) ? Carbon::createFromFormat('YmdHis', $message['dateTime']) : null,
                'user_check' => DB::table('clients')->where('username', $message['transactionReference'])->exists() ? 1 : 0
            ]);
        } else {
            // Log if transaction already exists to track potential duplicates
            Log::warning('Duplicate transaction received:', ['trxID' => $message['trxID']]);
        }



            // Extract username, amount, and transaction details
            $username = trim($message['transactionReference'] ?? '');
            $amount = $message['amount'] ?? null;
            $paymentDate = Carbon::now()->format('Y-m-d'); // Today's date
            $trxID = $message['trxID'] ?? null;

            // Find the client by username
            $client = Client::where('username', $username)->first();

            // Send Message after make payment
            $deelkoSMS = new DeelkoSMS();
            $deelkoSMS->sendSMS($client, 'sms_payment', $amount);

            if (!$client) {
                return response()->json(['error' => 'Client not found'], 404);
            }

            $clientId = $client->id;
            $plan = Plan::find($client->plan_id);

            if (!$plan) {
                return response()->json(['error' => 'Plan not found'], 404);
            }

            // Get all due invoices for the client
            $invoices = Invoice::where('client_id', $clientId)
                ->whereNull('deleted_at')
                ->where('due', '>', 0)
                ->orderBy('bill_year')
                ->orderBy('bill_month')
                ->get();

            DB::beginTransaction();

            try {
                $initialInvoice = $invoices->first();
                $billMonth = $initialInvoice ? $initialInvoice->bill_month : null;
                $billYear = $initialInvoice ? $initialInvoice->bill_year : null;

                $totalPaid = $amount;
                foreach ($invoices as $invoice) {
                    if ($amount <= 0) {
                        break;
                    }

                    $due = $invoice->due;
                    $payingAmount = min($amount, $due);

                    // Update the invoice
                    $invoice->paid_amount += $payingAmount;
                    $invoice->due -= $payingAmount;

                    if ($invoice->due == 0) {
                        // Deduct buy_price from reseller balance if due becomes 0
                        if ($client->resellerId) {
                            $reseller = Reseller::find($client->resellerId);
                            if ($reseller) {
                                $reseller->balance -= $invoice->buy_price;
                                $reseller->save();
                            }
                        }
                    }

                    $invoice->save();

                    $amount -= $payingAmount;
                }

                // Update client's due and balance
                if ($amount > 0) {
                    $client->balance += $amount;
                }

                $totalDue = Invoice::where('client_id', $clientId)
                    ->whereNull('deleted_at')
                    ->sum('due');
                $client->due = $totalDue;

                $paidAnyInvoice = $invoices->where('paid_amount', '>', 0)->count() > 0;

                // Update client status and expiration date if total due is 0
                if ($totalDue == 0 && $paidAnyInvoice) {
                    // Update Mikrotik
                    if($client->status = "Off"){
                        $mikrotik = new Mikrotik();
                        $activated = $mikrotik->activateClientInMikrotik($client);
                        $client->status = 'On';
                    }
                    $client->expiration = Carbon::now()->addMonth()->format('Y-m-d H:i:s');
                }

                

                // Update server status
                if ($client->server_satus == 2 && $client->status = "On") {
                    $client->server_satus = 1;
                }

            

                $client->save();

                // Create a client_payment record
                $user = User::find($client->resellerId);
                $userId = $user ? $user->id : null;

                $clientPayment = new ClientPayment();
                $clientPayment->client_id = $clientId;
                $clientPayment->bandwidth = $plan->plan_name;
                $clientPayment->bill_month = $billMonth;
                $clientPayment->bill_year = $billYear;
                $clientPayment->plan_price = $plan->plan_price;
                $clientPayment->paid_amount = $totalPaid;
                $clientPayment->new_paid = $totalPaid;
                $clientPayment->due = $totalDue;
                $clientPayment->user_id = $userId;
                $clientPayment->resellerId = $client->resellerId;
                $clientPayment->payment_date = $paymentDate;
                $clientPayment->save();

                // Create a transaction record
                $account = Account::where('account_type', 'bkash')
                    ->where('account_name', 'bkash')
                    ->whereNull('resellerId')
                    ->first();

                if (!$account) {
                    return response()->json(['error' => 'Bkash account not found'], 404);
                }

                $transaction = new Transaction();
                $transaction->account_id = $account->id;
                $transaction->tr_type = 'bill_payment';
                $transaction->tr_amount = $totalPaid;
                $transaction->payee = 'bkash_webhook';
                $transaction->payer = $client->name;
                $transaction->cr = $totalPaid;
                $transaction->bkash_trxID = $trxID;
                $transaction->tr_category = 'income';
                $transaction->user_id = $client->resellerId;
                $transaction->save();

                // Update the account balance
                $account->account_balance += $totalPaid;
                $account->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Transaction failed', 'message' => $e->getMessage()], 500);
            }

            return response()->json(['success' => 'Payment applied successfully']);
        }
    }
}
