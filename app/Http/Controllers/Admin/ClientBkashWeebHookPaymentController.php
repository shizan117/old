<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Config;
use App\ClientPayment;
use App\Invoice;
use App\Mikrotik;
use App\Reseller;
use App\SMS\DeelkoSMS;
use App\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Client;

class ClientController extends Controller
{
    public function payDueStore(Request $request, $id)
    {
        $client = Client::with([
            'plan' => function ($query) {
                $query->get();
            },
            'plan.server' => function ($query) {
                $query->get();
            }
        ])->find($id);

        $this->validate($request, [
            'due' => 'numeric',
            'paid_amount' => 'numeric|min:1',
            'paid_to' => 'required'
        ]);

        if (($request->paid_amount) == 0) {
            return redirect()->back()->withErrors([
                'paid_amount' => 'Payment amount must be greater than zero',
            ]);
        }

        $paid = $request->paid_amount;
        if ($client->due >= $request->paid_amount) {
            $ad_payment = 0.00;
            $due = $client->due - $request->paid_amount;
        } else {
            $ad_payment = $request->paid_amount - $client->due;
            $due = 0.00;
        }

        $new_balance = $client->balance + $ad_payment;

        $update_client = [
            'due' => $due,
            'balance' => $new_balance
        ];

        //GET LATEST DUE INVOICE
        $invoice = Invoice::where('client_id', $id)->orderBy('bill_year', 'DESC')->orderBy('bill_month', 'DESC')->where('due', '>', 0)->first();
        if (empty($invoice)) {
            $plan_price = 0.00;
            $service_charge = 0.00;
            $pre_due = $client->due;
            $vat = 0.00;
            $total = $pre_due;
            $discount = 0.00;

            $bandwidth = null;
            $bill_month = null;
            $bill_year = null;
            $invoice_id = null;
        } else {
            if ($invoice->paid_amount == 0) {
                $plan_price = $invoice->plan_price;
                $service_charge = $invoice->service_charge;
                $pre_due = $client->due - $invoice->due;
                $vat = $invoice->vat;
                $total = $invoice->total + $pre_due;
                $discount = $invoice->discount;
            } else {
                $plan_price = 0.00;
                $service_charge = 0.00;
                $pre_due = $client->due;
                $vat = 0.00;
                $total = $pre_due;
                $discount = 0.00;
            }

            $bandwidth = $invoice->bandwidth;
            $bill_month = $invoice->bill_month;
            $bill_year = $invoice->bill_year;
            $invoice_id = $invoice->id;
        }

        $sub_total = ($total + $ad_payment) - $discount;

        $payment_inputs = [
            'client_id' => $id,
            'bandwidth' => $bandwidth,
            'bill_month' => $bill_month,
            'bill_year' => $bill_year,
            'plan_price' => $plan_price,
            'advance_payment' => $ad_payment,
            'service_charge' => $service_charge,
            'pre_due' => $pre_due,
            'total' => $total + $ad_payment,
            'discount' => $discount,
            'all_total' => $sub_total,
            'vat' => $vat,
            'sub_total' => $sub_total + $vat,
            'paid_amount' => $paid,
            'new_paid' => $request->paid_amount,
            'paid_from_advance' => $request->pay_from_advance,
            'pre_balance' => $client->balance,
            'due' => $due,
            'user_id' => $id,
            'payment_date' => $request->payment_date,
            'branchId' => $client->branchId,
            'resellerId' => $client->resellerId
        ];

        $ac = Account::find($request->paid_to);
        $account_balance = $ac->account_balance + $request->paid_amount;
        $ac_inputs = [
            'account_balance' => $account_balance
        ];

        $update_client['status'] = 'On';

        if (Auth::user()->resellerId != '') {
            $reseller = Reseller::find(Auth::user()->resellerId);
            $vatRate = $reseller->vat_rate;
        } else {
            $vatData = Config::where('config_title', 'vatRate')->first();
            $vatRate = $vatData->value;
        }

        if ($vat > 0) {
            if ($request->paid_amount > $service_charge) {
                $p_price_paid = $request->paid_amount - $service_charge;
                $p_price_paid_without_vat = ceil(($p_price_paid * 100) / (100 + $vatRate));
                $vat_paid = $p_price_paid - $p_price_paid_without_vat;
            } else {
                $vat_paid = 0.00;
            }
        } else {
            $vat_paid = 0.00;
        }

        $tr_inputs = [
            'invoice_id' => $invoice_id,
            'account_id' => $request->paid_to,
            'tr_type' => 'Bill Payment',
            'tr_category' => 'Income',
            'tr_amount' => $request->paid_amount,
            'tr_vat' => $vat_paid,
            'payer' => $client->client_name,
            'cr' => $request->paid_amount,
            'user_id' => Auth::user()->id,
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId,
            'trans_date' => $request->payment_date
        ];

        if ($client->start_transaction == "") {
            $update_client['start_transaction'] = date("Y-m-d");
        }

        $print_receipt = Config::where('config_title', 'print_receipt_after_payment')->first()->value;


        DB::beginTransaction();
        try {


            //IF NO DUE INVOICE FOUND
            if (empty($invoice)) {

                $tr_id = Transaction::create($tr_inputs)->id;
                if (!empty($tr_id)) {
                    $payment_inputs['tr_id'] = $tr_id;
                    if ($payment_id = ClientPayment::create($payment_inputs)->id) {
                        if ($client->update($update_client)) {
                            $ac->update($ac_inputs);

                            //========PAYMENT CONFIRMATION SMS=======
                            $deelkoSMS = new DeelkoSMS();
                            $deelkoSMS->sendSMS($client, 'sms_payment', $request->paid_amount);
                            if ($print_receipt == 'Yes') {
                                DB::commit();
                                return redirect()->route('receipt.print', $payment_id)->with(['redirect' => true]);
                            } else {
                                DB::commit();
                                Session::flash('message', 'Client\'s Due Paid Successful');
                                Session::flash('m-class', 'alert-success');
                                return redirect()->route('client.index');
                            }
                        } else {
                            throw new \Exception('Client Data Update Failed!');
                        }
                    } else {
                        throw new \Exception('Client Payment Create Failed!');
                    }
                } else {
                    throw new \Exception('Transaction Create Failed!');
                }
            } //IF DUE INVOICE EXIST
            else {
                //GET ALL DUE INVOICES
                $dueInvoices = Invoice::where('client_id', $id)->where('due', '>', 0)
                    ->orderBy('bill_year', 'ASC')->orderBy('bill_month', 'ASC')->get();

                $_updated_invoices = [];
                //LOOPING OVER ALL DUE INVOICES
                foreach ($dueInvoices as $singleInvoice) {
                    //COUNTING DUE INVOICE
                    $invoiceCount = Invoice::where('client_id', $id)->where('due', '!=', 0)->count();

                    if ($paid > $singleInvoice['due']) {
                        $paid_amount = $singleInvoice['due'];
                    } else {
                        $paid_amount = $paid;
                    }

                    $inputs = [
                        'paid_amount' => $singleInvoice['paid_amount'] + $paid_amount,
                        'due' => $singleInvoice['due'] - $paid_amount
                    ];

                    if ($request->exp_date != '') {
                        $exp_time = explode(':', setting('exp_time'));
                        $date_exp = date($request->exp_date . " " . "$exp_time[0]:$exp_time[1]:00");
                        $update_client['expiration'] = $date_exp;
                    } else {
                        $current_month = date('Y-m');
                        $invoice_month_value = date_create($singleInvoice->bill_year . '-' . $singleInvoice->bill_month);
                        $invoice_month = date_format($invoice_month_value, 'Y-m');

                        if ($current_month == $invoice_month) {

                            //DETERMINE EXPIRE DATE OF MONTH
                            $invoice_system = Config::where('config_title', 'invoice_system')->first()->value;
                            if (Auth::user()->resellerId != '') {
                                $reseller = Reseller::find(Auth::user()->resellerId);
                                if ($invoice_system == 'fixed') {
                                    $c_exp_date = $reseller->c_exp_date;
                                } else {
                                    $isExpired = strtotime(now()) - strtotime($client->expiration);
                                    if ($isExpired >= 0) {
                                        $c_exp_date = date('d');
                                    } else {
                                        $c_exp_date = date('d', strtotime($client->expiration));
                                    }
                                }
                            } else {
                                $c_exp_value = Config::where('config_title', 'exp_date')->first();
                                if ($invoice_system == 'fixed') {
                                    $c_exp_date = $c_exp_value->value;
                                } else {
                                    $isExpired = strtotime(now()) - strtotime($client->expiration);
                                    if ($isExpired >= 0) {
                                        $c_exp_date = date('d');
                                    } else {
                                        $c_exp_date = date('d', strtotime($client->expiration));
                                    }
                                }
                            }

                            $exp_time = explode(':', setting('exp_time'));
                            $date_exp = date("Y-m-d H:i:s", mktime(date($exp_time[0]), date($exp_time[1]), date("00"), date("m") + $singleInvoice->duration, date($c_exp_date), date("Y")));

                            $update_client['expiration'] = $date_exp;
                        }
                    }

                    $inv = Invoice::find($singleInvoice['id']);

                    //STORING DATA FOR INVOICE ROLLBACK
                    if ($invoiceCount > 1) {
                        $_updated_invoices[$inv->id] = [
                            'paid_amount' => $inv->paid_amount,
                            'due' => $inv->due
                        ];
                    }

                    if ($client->resellerId != null) {
                        $reseller_data = [];
                        if ($inv->paid_amount == 0) {
                            if ($inv->buy_price > $reseller->balance) {
                                throw new \Exception('Reseller balance is low to take payment. Contact with upstream!');
                            }
                            $reseller_data = [
                                'balance' => $reseller->balance - $inv->buy_price
                            ];
                        }
                    }

                    $paid = $paid - $singleInvoice['due'];


                    //IF PAID AMOUNT DEDUCTION BECAME ZERO OR DUE INVOICE LOOPING ENDED
                    if ($paid <= 0 || !($invoiceCount > 1)) {
                        //IF CLIENT IS INACTIVE

                        if ($client->status == 'Off') {

                            if ($client->server_status == 2) {
                                $update_client['server_status'] = 1;
                                $update_client['server_active_date'] = date('Y-m-d');
                            }

                            if (setting('using_mikrotik')) {

                                //IF CLIENT NOT EXIST IN MIKROTIK
                                if ($client->server_status == 2) {
                                    $mikrotik = new Mikrotik();
                                    $added = $mikrotik->addClientToMikrotik($client, $client->plan);
                                    if (!$added) {
                                        throw new \Exception('Mikrotik Error');
                                    }
                                } //IF CLIENT EXIST BUT NOT ACTIVE IN MIKROTIK
                                else {
                                    $mikrotik = new Mikrotik();
                                    $activated = $mikrotik->activateClientInMikrotik($client);
                                    if (!$activated) {
                                        throw new \Exception('Mikrotik Error');
                                    }
                                }
                            }
                        }

                        //FINALLY UPDATE RELATED DATAS
                        $tr_id = Transaction::create($tr_inputs)->id;
                        if (!empty($tr_id)) {
                            $payment_inputs['tr_id'] = $tr_id;
                            if ($payment_id = ClientPayment::create($payment_inputs)->id) {
                                $inv->update($inputs);
                                if ($client->update($update_client)) {
                                    $ac->update($ac_inputs);
                                    if ($client->resellerId != null) {
                                        $reseller->update($reseller_data);
                                    }

                                    //========PAYMENT CONFIRMATION SMS=======
                                    $deelkoSMS = new DeelkoSMS();
                                    $deelkoSMS->sendSMS($client, 'sms_payment', $request->paid_amount);

                                    if ($print_receipt == 'Yes') {
                                        DB::commit();
                                        return redirect()->route('receipt.print', $payment_id)->with(['redirect' => true]);
                                    } else {
                                        DB::commit();
                                        Session::flash('message', 'Client\'s Due Paid Successful');
                                        Session::flash('m-class', 'alert-success');
                                        return redirect()->route('client.due');
                                    }
                                } else {
                                    throw new \Exception('Client Data Update Failed!');
                                }
                            } else {
                                throw new \Exception('Client Payment Create Failed!');
                            }
                        } else {
                            throw new \Exception('Transaction Create Failed!');
                        }
                    }


                    $inv->update($inputs);
                }
                //ENDOF LOOPING OVER ALL DUE INVOICES

            }
        } catch (\Throwable $e) {
            DB::rollBack();
            if (!empty($_updated_invoices)) {
                foreach ($_updated_invoices as $key => $u_invoice) {
                    $_inv = Invoice::find($key);
                    $_inv->update($u_invoice);
                }
            }
            Session::flash('message', $e->getMessage());
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('client.index');
        }
    }
}
