<?php

namespace App\Http\Controllers;

use App\Account;
use App\Client;
use App\ClientPayment;
use App\Config;
use App\Invoice;
use App\Mikrotik;
use App\Plan;
use App\ResellerPlan;
use App\RocketBillPayTransaction;
use App\SMS\DeelkoSMS;
use App\Transaction;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;

class RocketBillPayController extends Controller
{

    public function paymentValidation(Request $request)
    {
        // Check for refNo in body or header
        $refNo = $request->input('refNo') ?? $request->header('refNo');
        
         // Get API credentials from body or header
    $apiUsername = $request->input('api_username') ?? $request->header('api_username');
    $apiPassword = $request->input('api_password') ?? $request->header('api_password');
    
        if (!$refNo) {
            return response()->json([
                'errCode' => 400,
                'errMsg' => 'refNo is required',
            ], 400);
        }
        
         // Validate API credentials
    if ($apiUsername !== 'Deelko@' || $apiPassword !== '@7[Deelko@2#*4') {
        return response()->json([
            'errCode' => 401,
            'errMsg' => 'Unauthorized: Invalid credentials',
        ], 401);
    }

        // Validate the length of refNo
        if (strlen($refNo) > 25) {
            return response()->json([
                'errCode' => 400,
                'errMsg' => 'Ref Length exceeds limit',
            ], 400);
        }

        $username = $refNo;
        $total_due_invoice_amount = 0;

        // Check if the client exists
        $client = Client::where('username', $username)->first();
        if (!$client) {
            return response()->json([
                'errCode' => 404,
                'errMsg' => 'Client Not Found!',
            ], 404);
        }

        // Calculate total due invoice amount for the client
        $invoices = Invoice::where('client_id', $client->id)
            ->where('due', '>', 0)
            ->whereNull('deleted_at')
            ->get();

        $total_due_invoice_amount = $invoices->sum('due');
        $client_profile_due = $client->due;

        if ($total_due_invoice_amount != $client_profile_due) {
            // resolve invoice missmatch issue
            $client->due = $total_due_invoice_amount;
            $client->save();
        }
        return response()->json([
            'errCode' => 0,
            'errMsg' => 'Client Found!',
            'clientName' => $client->client_name,
            'username' => $client->username,
            'due' => $client->due,
        ], 200);
    }

    public function paymentConfirmation(Request $request)
    {
        // Check for txnId and username in body or header
        $txnId = $request->input('txnId') ?? $request->header('txnId');
        $username = $request->input('username') ?? $request->header('username');
        $amount = $request->input('amount') ?? $request->header('amount');
        
        // Get API credentials from body or header
    $apiUsername = $request->input('api_username') ?? $request->header('api_username');
    $apiPassword = $request->input('api_password') ?? $request->header('api_password');

        if (!$txnId || !$username || !$amount) {
            return response()->json([
                'errCode' => 400,
                'errMsg' => 'txnId, username & amount are required',
            ], 400);
        }
        
        // Validate API credentials
    if ($apiUsername !== 'Deelko@' || $apiPassword !== '@7[Deelko@2#*4') {
        return response()->json([
            'errCode' => 401,
            'errMsg' => 'Unauthorized: Invalid credentials',
        ], 401);
    }

        if (strlen($txnId) > 15) {
            return response()->json([
                'errCode' => 400,
                'errMsg' => 'txnId Length exceeds limit',
            ], 400);
        }

        if (strlen($username) > 25) {
            return response()->json([
                'errCode' => 400,
                'errMsg' => 'username Length exceeds limit',
            ], 400);
        }

        $client = Client::where('username', $username)->first();
        if(!$client){
            return response()->json([
                'errCode' => 404,
                'errMsg' => 'Client not found!',
            ], 404);
        }

        // check alrady paid entry
        $check = RocketBillPayTransaction::where('rocket_transaction_id', $txnId)->first();
        if ( $check && $check->is_paid == 1) {
            return response()->json([
                'errCode' => 0,
                'errMsg' => 'Already Paid!',
            ], 200);
        }

        $response = $this->updateClientBill($txnId, $client, $amount);

        if ($response) {
            // Return success response
            return response()->json([
                'errCode' => 0,
                'errMsg' => 'Payment Information Updated Successfully!',
            ], 200);
        }

        // Return success response
        return response()->json([
            'errCode' => 0,
            'errMsg' => 'Failed to update the payment information!',
        ], 500);
    }


    function updateClientBill($trxID, $client, $amount)
    {
        // Fetch all invoices with due amounts for the specified client
        $all_due_invoices = Invoice::where('client_id', $client->id)
            ->where('due', '>', 0)
            ->whereNull('deleted_at')
            ->get();

        $total_due = $all_due_invoices->sum('due');
        $last_invoice_id = Invoice::where('client_id', $client->id)
            ->where('due', '>', 0)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->value('id');

        $latest_due = Invoice::where('client_id', $client->id)
            ->where('due', '>', 0)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->value('due');

        $pre_due = $total_due - ($latest_due ?? 0);
        $currentDate = now()->toDateString();

        try {
            // Start Transaction
            DB::beginTransaction();

            // Make due = 0 for all invoices
            foreach ($all_due_invoices as $invoice) {
                $invoice->paid_amount = $invoice->due;
                $invoice->due = 0;
                $invoice->save();
            }

            // Update bkash account balence
            $accountInfo = null;
            if ($client->resellerId) {
                $accountInfo = Account::where('account_type', 'rocket')
                    ->where('account_name', 'rocket')
                    ->where('resellerId', $client->resellerId)
                    ->first();
            } else {
                $accountInfo = Account::where('account_type', 'rocket')
                    ->where('account_name', 'rocket')
                    ->whereNull('resellerId')
                    ->first();
            }

            $pre_balence = $accountInfo->account_balance;
            $accountInfo->account_balance = $pre_balence + $amount;
            $accountInfo->save();

            // Create a new transaction
            $transaction = new Transaction();
            $transaction->account_id = $accountInfo->id;
            $transaction->tr_type = "Bill Payment";
            $transaction->tr_category = "income";
            $transaction->payer = $client->client_name;
            $transaction->cr = $amount;
            $transaction->tr_amount = $amount;
            $transaction->txnId = $trxID;
            $transaction->user_id = $client->resellerId ?? null;
            $transaction->invoice_id = $last_invoice_id;
            $transaction->trans_date = $currentDate;
            $transaction->save();

            // Log transaction

            // Create a new client payment
            $client_payment = new ClientPayment();
            $client_payment->client_id = $client->id;
            $client_payment->bandwidth = Plan::find($client->plan_id)->value('plan_name');
            $client_payment->bill_month = date('n');
            $client_payment->bill_year = date('Y');
            $client_payment->plan_price = $client->resellerId
                ? ResellerPlan::where('resellerId', $client->resellerId)
                ->where('plan_id', $client->plan_id)
                ->value('reseller_sell_price')
                : Plan::find($client->plan_id)->value('plan_price');
            $client_payment->pre_due = $pre_due;
            $client_payment->total = $amount;
            $client_payment->all_total = $amount;
            $client_payment->sub_total = $amount;
            $client_payment->paid_amount = $amount;
            $client_payment->new_paid = $amount;
            $client_payment->due = 0;
            $client_payment->tr_id = $transaction->id;
            $client_payment->payment_date = $currentDate;
            $client_payment->resellerId = $client->resellerId ?? null;
            if ($total_due < $amount) {
                $client_payment->advance_payment = $amount - $total_due; // add advance amount
            }
            $client_payment->save();

            // Create Online Transaction
            $onlinePay = new RocketBillPayTransaction();
            $onlinePay->date = $currentDate;
            $onlinePay->username = $client->username;
            $onlinePay->transaction_id = $transaction->id;
            $onlinePay->rocket_transaction_id = $trxID;
            $onlinePay->is_paid = 1;
            $onlinePay->amount = $amount;
            $onlinePay->save();

            // Update client profile
            $client->due = 0;
            if ($client->due == 0) {
                $client->balance +=  ($amount - $total_due); // add advance amount
            }

            // Send SMS
            $deelkoSMS = new DeelkoSMS();
            $deelkoSMS->sendSMS($client, 'sms_payment', $amount);

            // Activate client in Mikrotik if needed
            if ($client->status === "Off") {
                $mikrotik = new Mikrotik();
                $mikrotik->activateClientInMikrotik($client);
                $client->status = 'On';
            }

            $invoice_type = Config::where('config_title', 'invoice_system')->value('value');
            if ('dynamic' == $invoice_type) {
                $expirationDate = Carbon::parse($client->expiration);
                $currentDate = Carbon::now();
                if ($currentDate->greaterThan($expirationDate)) {
                    $client->expiration = $currentDate->addMonth()->format('Y-m-d H:i:s');
                } else {
                    $client->expiration = $expirationDate->addMonth()->format('Y-m-d H:i:s');
                }
            } else {
                $system_expire_date = Config::where('config_title', 'exp_date')->value('value');
                $system_expire_time = Config::where('config_title', 'exp_time')->value('value');

                $currentDate = Carbon::now();
                $new_expire_date = Carbon::createFromDate($currentDate->year, $currentDate->month, $system_expire_date)
                    ->addMonth() // Move to the next month
                    ->setTimeFromTimeString($system_expire_time) // Set the time from the config
                    ->format('Y-m-d H:i:s');
                $client->expiration = $new_expire_date;
            }

            $client->save();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    public function getPaymentStatus(Request $request)
    {
        $txnId = $request->input('txnId') ?? $request->header('txnId');
         
        // Get API credentials from body or header
    $apiUsername = $request->input('api_username') ?? $request->header('api_username');
    $apiPassword = $request->input('api_password') ?? $request->header('api_password');
    
        $transaction = Transaction::where('txnId', $txnId)
            ->latest()
            ->first();

        if (!$transaction) {
            return response()->json([
                'errCode' => 404,
                'errMsg' => 'Transaction Not Found!',
            ], 404);
        }
        
        // Validate API credentials
    if ($apiUsername !== 'Deelko@' || $apiPassword !== '@7[Deelko@2#*4') {
        return response()->json([
            'errCode' => 401,
            'errMsg' => 'Unauthorized: Invalid credentials',
        ], 401);
    }
    
        return response()->json([
            'errCode' => 0,
            'errMsg' => 'Payment Information Updated Successfully!',
        ], 200);
    }
}
