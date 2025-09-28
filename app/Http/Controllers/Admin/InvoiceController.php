<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Client;
use App\ClientPayment;
use App\Config;
use App\Invoice;
use App\Mikrotik;
use App\Reseller;
use App\SMS\DeelkoSMS;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use MikrotikAPI\Commands\IP\Hotspot\HotspotUsers;
use MikrotikAPI\Commands\IP\Hotspot\HotspotActive;
use MikrotikAPI\Commands\IP\Firewall\FirewallAddressList;
use MikrotikAPI\Commands\IP\ARP;
use MikrotikAPI\Commands\PPP\Secret;
use MikrotikAPI\Commands\PPP\Active;
use MikrotikAPI\Commands\Queues\Simple;
use MikrotikAPI\Roar\Roar;

class InvoiceController extends Controller
{
    private $from_date;
    private $to_date;
    private $range;
    public function __construct()
    {
        $this->range = [Carbon::now()->startOfMonth() . ' 00:00:00', Carbon::now()->endOfMonth() . ' 23:59:59'];
        if (\request()->from_date != '') {
            $this->from_date = \request()->from_date ?? date("Y-m-d");
            $this->to_date = \request()->to_date ?? date("Y-m-d");
            $this->range = [$this->from_date . ' 00:00:00', $this->to_date . ' 23:59:59'];
        }
    }

    public function index(Request $request)
    {
        if (Auth::user()->branchId != '') {
            $invoiceData = Invoice::with(['client'])->whereHas("client")
                ->where("branchId", Auth::user()->branchId);
        } else {
            $invoiceData = Invoice::with(['client'])
                ->whereHas("client")->where('resellerId', Auth::user()->resellerId)
                ->where("branchId", Auth::user()->branchId);
        }

        //	    if($request->from_date != ''){
        //		    $invoiceData =  $invoiceData->whereBetween('created_at',$this->range);
        //	    }
        $invoiceData = $invoiceData->whereBetween('created_at', $this->range)
            ->orderBy('due', 'DESC')->get();

        $page_title = "This Month Invoice List";
        $admin = false;

        return view('admin.pages.invoice.index', compact('invoiceData', 'page_title', 'admin'));
    }

    public function branchesInvoiceList()
    {
        $invoiceData = Invoice::with(['client'])->whereHas("client")
            ->whereNotNull("branchId")->orderBy('created_at', 'DESC')->get();
        $page_title = "Branches Client's Invoice List";


        return view('admin.pages.invoice.index', compact('invoiceData', 'page_title', 'admin'));
    }

    public function dueInvoice(Request $request)
    {
        if (Auth::user()->branchId != '') {
            $invoiceData = Invoice::whereHas("client")->where("branchId", Auth::user()->branchId)
                ->where('due', '>', 0)->orderBy('created_at', 'DESC');
        } else {
            $invoiceData = Invoice::whereHas("client")->where('due', '>', 0)
                ->where('resellerId', Auth::user()->resellerId)
                ->where("branchId", Auth::user()->branchId)
                ->orderBy('created_at', 'DESC');
        }

        if ($request->from_date != '') {
            $invoiceData =  $invoiceData->whereBetween('created_at', $this->range);
        }
        $invoiceData = $invoiceData->get();

        $page_title = "All Client's Due Invoice List";

        $admin = false;

        return view('admin.pages.invoice.index', compact('invoiceData', 'page_title', 'admin'));
    }

    public function branchesDueInvoiceList()
    {
        $invoiceData = Invoice::with(['client'])->whereHas("client")->whereNotNull("branchId")
            ->where('due', '>', 0)->orderBy('created_at', 'DESC')->get();

        $page_title = "Branches Client's Due Invoice List";
        $admin = true;
        return view('admin.pages.invoice.index', compact('invoiceData', 'page_title', 'admin'));
    }

    //    public function resellerInvoiceList()
    //    {
    //        $role_id = Auth::user()->roleId;
    //        if ($role_id == 4) {
    //            $invoiceData = ResellerInvoice::with(['reseller'])->where('resellerId', Auth::user()->resellerId)->orderBy('created_at', 'DESC')->get();
    //            $page_title = "Seller's Invoice List";
    //        } else {
    //            $invoiceData = ResellerInvoice::with(['reseller'])->orderBy('created_at', 'DESC')->get();
    //            $page_title = "Reseller's Invoice List";
    //        }
    //        return view('admin.pages.reseller_invoice_list', compact('invoiceData', 'page_title', 'role_id'));
    //    }

    public function create()
    {
        $clients = Client::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->where('status', 'On')->orderBY('id', 'ASC')->get();

        $accounts = Account::where(function ($query) {
            $query->where('branchId', Auth::user()->branchId)
                ->where('resellerId', Auth::user()->resellerId);
        })
            ->orwhere(function ($query) {
                $query->whereNull('branchId')
                    ->where('resellerId', Auth::user()->resellerId)
                    ->where('account_type', '!=', 'Cash');
            })
            ->orderBY('id', 'ASC')->get();

        $reseller_has_extra_charge = Reseller::where('resellerId',Auth::user()->resellerId)
            ->value('extra_charge');  // fetch only extra_charge column


        return view('admin.pages.invoice.create', compact('clients', 'accounts','reseller_has_extra_charge'));
    }
    public function checkClientPayment(Request $request)
    {
        $clientId = $request->client_id;

        //Log::info(['client_id' => $clientId]);

        $otcInvoiceExists = \App\Invoice::where('client_id', $clientId)
            ->where('otc_charge', '>', 0)

            ->exists();

       // Log::info('OTC invoice exists: ' . ($otcInvoiceExists ? 'true' : 'false'));

        $hasPayment = \App\ClientPayment::where('client_id', $clientId)->exists();

      //  Log::info('Client has payment: ' . ($hasPayment ? 'true' : 'false'));

        return response()->json([
            'has_payment' => $hasPayment || $otcInvoiceExists
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'client_id' => 'required',
            'plan_price' => 'numeric',
            'total' => 'numeric|min:1',
            'service_charge' => 'numeric|min:0',
            'discount' => 'required|numeric|lte:total',
            'sub_total' => 'numeric|min:0',
            'paid' => 'numeric',
            'otc_charge' => 'numeric|min:0'
        ]);
      //  dd($request->client_id);

        // check korbe ei client age jekono tk diye thake tahole ar dite hobe na otc
        if (
            $request->otc_charge >0 &&
            \App\ClientPayment::where('client_id', $request->client_id)->exists()
        ) {
            $request->otc_charge=0.00;
        }

        //      CHECK IF THIS MONTH INVOICE EXIST
        $inv_month = Carbon::createFromDate($request->bill_year, $request->bill_month);
        $now = Carbon::now();
        if ($inv_month > $now) {
            Session::flash('message', 'You can not create next months invoice!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }

        //      GET CLIENT
        $client = Client::with([
            'plan' => function ($query) {
                $query->get();
            },
            'plan.server' => function ($query) {
                $query->get();
            }
        ])->findOrFail($request->client_id);

        if ($request->paid > 0) {
            if ($request->paid_to == '') {
                Session::flash('message', 'Select Paid To!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
        //      CHECK IF THIS MONTH INVOICE EXIST
        $inv = Invoice::withTrashed()->where([
            ['client_id', '=', $request->client_id],
            ['bill_month', '=', $request->bill_month],
            ['bill_year', '=', $request->bill_year]
        ])->first();

        if (!empty($inv)) {
            if ($inv->deleted_at != '') {
                Session::flash('message', 'You already created and deleted this invoice before!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
            Session::flash('message', 'This Month Invoice Already Created for This Client!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }

        if ($request->paid > $request->sub_total) {
            $paid = $request->sub_total;
        } else {
            $paid = $request->paid;
        }

        $pre_due = $client->due;
        $all_total = $request->sub_total + $pre_due; //previous due+ current sub total

        //Determine balance & due
        if ($all_total >= $request->paid) {
            $ad_payment = 0.00;
            $due = $all_total - $request->paid;
        } else {
            $ad_payment = $request->paid - $all_total;
            $due = 0.00;
        }
        $new_balance = $client->balance + $ad_payment;

        $pre_balance = $client->balance;

        $update_client = [
            'due' => $due,
            'balance' => $new_balance
        ];


        if (Auth::user()->resellerId != '') {

            $buy_price = $request->buy_plan_price;
            $reseller = Reseller::find(Auth::user()->resellerId);
            $vatRate = $reseller->vat_rate;
        } else {
            $vatData = Config::where('config_title', 'vatRate')->first();
            $vatRate = $vatData->value;
            $buy_price = 0;
        }

        $price = ceil((($request->plan_price - $request->discount) * 100) / (100 + $vatRate));
        $vat = $request->plan_price - $price - $request->discount;
        $plan_price = $request->plan_price - $vat;

        $inputs = [
            'client_id' => $request->client_id,
            'bandwidth' => $request->bandwidth,
            'bill_month' => $request->bill_month,
            'bill_year' => $request->bill_year,
            'buy_price' => $buy_price,
            'plan_price' => $plan_price,
            'service_charge' => $request->service_charge,
            'charge_for' => $request->charge_for,
            'otc_charge' => $request->otc_charge ?? 0.00,
            'total' => $request->total - $vat,
            'discount' => $request->discount,
            'all_total' => $request->sub_total - $vat,
            'vat' => $vat,
            'sub_total' => $request->sub_total,
            'paid_amount' => $paid,
            'due' => $request->sub_total - $paid,
            'duration' => $client->plan->duration,
            'duration_unit' => $client->plan->duration_unit,
            'branchId' => $client->branchId,
            'resellerId' => $client->resellerId,
        ];

        //========== COLLECT PAYMENT WHEN INVOICE CREATE ============
        $invoice_system = Config::where('config_title', 'invoice_system')->first()->value;
        if ($request->paid > 0) {
            $total = $all_total - $request->discount;

            if (Auth::user()->resellerId != '') {
                $vatRate = $reseller->vat_rate;
                if ($buy_price > $reseller->balance) {
                    Session::flash('message', 'You balance is low to pay invoice. Contact with upstream!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
                $reseller_data = [
                    'balance' => $reseller->balance - $buy_price
                ];
            }

            //Calculate Expire Date
            $current_month = date('Y-m');
            $invoice_month_value = date_create($request->bill_year . '-' . $request->bill_month);
            $invoice_month = date_format($invoice_month_value, 'Y-m');

            if ($current_month == $invoice_month) {

                //DETERMINE EXPIRE DATE OF MONTH
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
                $date_exp = date("Y-m-d H:i:s", mktime(date($exp_time[0]), date($exp_time[1]), date("00"), date("m") + $client->plan->duration, date($c_exp_date), date("Y")));

                $update_client['expiration'] = $date_exp;
            }

            $payment_inputs = [
                'client_id' => $request->client_id,
                'bandwidth' => $request->bandwidth,
                'bill_month' => $request->bill_month,
                'bill_year' => $request->bill_year,
                'plan_price' => $plan_price,
                'service_charge' => $request->service_charge,
                'otc_charge' => $request->otc_charge ?? 0.00,
                'advance_payment' => $ad_payment,
                'pre_due' => $pre_due,
                'total' => $total - $vat,
                'discount' => $request->discount,
                'all_total' => $all_total - $vat,
                'vat' => $vat,
                'sub_total' => $all_total,
                'paid_amount' => $request->paid,
                'new_paid' => $request->paid,
                'paid_from_advance' => 0.00,
                'pre_balance' => $pre_balance,
                'due' => $due,
                'payment_date' => date('Y-m-d'),
                'user_id' => Auth::user()->id,
                'branchId' => $client->branchId,
                'resellerId' => $client->resellerId
            ];

            $ac = Account::find($request->paid_to);
            $account_balance = $ac->account_balance + $request->paid;
            $ac_inputs = [
                'account_balance' => $account_balance
            ];

            if ($request->paid > $request->service_charge) {
                $p_price_paid = $request->paid - $request->service_charge;
                $p_price_paid_without_vat = ceil(($p_price_paid * 100) / (100 + $vatRate));
                $vat_paid = $p_price_paid - $p_price_paid_without_vat;
            } else {
                $vat_paid = 0.00;
            }

            $tr_inputs = [
                'account_id' => $request->paid_to,
                'tr_type' => 'Bill Payment',
                'tr_category' => 'Income',
                'tr_amount' => $request->paid,
                'tr_vat' => $vat_paid,
                'charge' => 0,
                'payer' => $client->client_name,
                'cr' => $request->paid,
                'user_id' => Auth::user()->id,
                'branchId' => Auth::user()->branchId,
                'resellerId' => Auth::user()->resellerId,
                'trans_date' => date('Y-m-d')
            ];

            if ($client->start_transaction == "") {
                $update_client['start_transaction'] = date("Y-m-d");
            }

            DB::beginTransaction();
            try {
                $invoiceId = Invoice::create($inputs)->id;
                if (!empty($invoiceId)) {
                    //========NEW INVOICE CREATE SMS=======
                    $deelkoSMS = new DeelkoSMS();
                    $deelkoSMS->sendSMS($client, 'sms_invoice', null, $due);

                    $tr_inputs['invoice_id'] = $invoiceId;
                    $tr_id = Transaction::create($tr_inputs)->id;
                    if (!empty($tr_id)) {
                        $payment_inputs['tr_id'] = $tr_id;
                        if (ClientPayment::create($payment_inputs)) {
                            if ($client->update($update_client)) {
                                $ac->update($ac_inputs);
                                //========PAYMENT SMS=======
                                $deelkoSMS = new DeelkoSMS();
                                $deelkoSMS->sendSMS($client, 'sms_payment', $request->paid);

                                if (Auth::user()->resellerId != '') {
                                    $reseller->update($reseller_data);
                                }

                                DB::commit();
                                Session::flash('message', 'Invoice Created Successful!');
                                Session::flash('m-class', 'alert-success');
                                return redirect()->route('invoice.index');
                            } else {
                                throw new \Exception('Client Data Update Failed!');
                            }
                        } else {
                            throw new \Exception('Client Payment Create Failed!');
                        }
                    } else {
                        throw new \Exception('Transaction Create Failed!');
                    }
                } else {
                    throw new \Exception('Invoice Create Failed!');
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Session::flash('message', $e->getMessage());
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
        //========== PAY FROM ADVANCE IF CLIENT HAS PREVIOUS ADVANCE BALANCE ============
        else if ($client->balance > 0) {

            if ($client->balance > $request->sub_total) {
                $paid = $request->sub_total;
            } else {
                $paid = $client->balance;
            }

            //           $pre_due = $client->due;
            //           $all_total = $request->sub_total + $pre_due; //previous due+ current sub total
            //Determine balance & due
            if ($all_total >= $client->balance) {
                $ad_payment = 0.00;
                $due = $all_total - $client->balance;
            } else {
                $ad_payment = 0.00;
                $due = 0.00;
            }
            $new_balance = $client->balance - $paid;

            //          $pre_balance = $client->balance;
            $update_client = [
                'due' => $due,
                'balance' => $new_balance
            ];

            $inputs = [
                'client_id' => $request->client_id,
                'bandwidth' => $request->bandwidth,
                'bill_month' => $request->bill_month,
                'bill_year' => $request->bill_year,
                'buy_price' => $buy_price,
                'plan_price' => $plan_price,
                'service_charge' => $request->service_charge,
                'charge_for' => $request->charge_for,
                'otc_charge' => $request->otc_charge ?? 0.00,
                'total' => $request->total - $vat,
                'discount' => $request->discount,
                'all_total' => $request->sub_total - $vat,
                'vat' => $vat,
                'sub_total' => $request->sub_total,
                'paid_amount' => $paid,
                'due' => $request->sub_total - $paid,
                'duration' => $client->plan->duration,
                'duration_unit' => $client->plan->duration_unit,
                'branchId' => $client->branchId,
                'resellerId' => $client->resellerId
            ];

            $total = $all_total - $request->discount;

            if (Auth::user()->resellerId != '') {
                if ($buy_price > $reseller->balance) {
                    Session::flash('message', 'You balance is low to pay invoice. Contact with upstream!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
                $reseller_data = [
                    'balance' => $reseller->balance - $buy_price
                ];
            }

            //Calculate Expire Date
            $current_month = date('Y-m');
            $invoice_month_value = date_create($request->bill_year . '-' . $request->bill_month);
            $invoice_month = date_format($invoice_month_value, 'Y-m');

            if ($current_month == $invoice_month) {

                //DETERMINE EXPIRE DATE OF MONTH
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
                $date_exp = date("Y-m-d H:i:s", mktime(date($exp_time[0]), date($exp_time[1]), date("00"), date("m") + $client->plan->duration, date($c_exp_date), date("Y")));

                $update_client['expiration'] = $date_exp;
            }

            $payment_inputs = [
                'client_id' => $request->client_id,
                'bandwidth' => $request->bandwidth,
                'bill_month' => $request->bill_month,
                'bill_year' => $request->bill_year,
                'plan_price' => $plan_price,
                'service_charge' => $request->service_charge,
                'otc_charge' => $request->otc_charge ?? 0.00,
                'advance_payment' => $ad_payment,
                'pre_due' => $pre_due,
                'total' => $request->total + $pre_due,
                'discount' => $request->discount,
                'all_total' => $all_total - $vat,
                'vat' => $vat,
                'sub_total' => $all_total,
                'paid_amount' => $paid,
                'new_paid' => 0.00,
                'paid_from_advance' => $paid,
                'pre_balance' => $pre_balance,
                'due' => $due,
                'payment_date' => date('Y-m-d'),
                'user_id' => Auth::user()->id,
                'branchId' => $client->branchId,
                'resellerId' => $client->resellerId
            ];

            if ($client->start_transaction == "") {
                $update_client['start_transaction'] = date("Y-m-d");
            }

            DB::beginTransaction();
            try {
                $invoiceId = Invoice::create($inputs)->id;
                //========NEW INVOICE CREATE SMS=======
                $deelkoSMS = new DeelkoSMS();
                $deelkoSMS->sendSMS($client, 'sms_invoice', null, $due);

                if (!empty($invoiceId)) {
                    if (ClientPayment::create($payment_inputs)) {
                        if ($client->update($update_client)) {

                            if (Auth::user()->resellerId != '') {
                                $reseller->update($reseller_data);
                            }

                            DB::commit();
                            Session::flash('message', 'Invoice Created Successful!');
                            Session::flash('m-class', 'alert-success');
                            return redirect()->route('invoice.index');
                        } else {
                            throw new \Exception('Client Data Update Failed!');
                        }
                    } else {
                        throw new \Exception('Client Payment Create Failed!');
                    }
                } else {
                    throw new \Exception('Invoice Create Failed!');
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Session::flash('message', $e->getMessage());
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        } else {
            $invoiceId = Invoice::create($inputs)->id;
            if (!empty($invoiceId)) {
                //========NEW INVOICE CREATE SMS=======
                $deelkoSMS = new DeelkoSMS();
                $deelkoSMS->sendSMS($client, 'sms_invoice', null, $due);

                if ($client->update($update_client)) {
                    //                    if (Auth::user()->resellerId != '') {
                    ////                        $reseller->update($reseller_data);
                    //                    }
                    Session::flash('message', 'Invoice Created Successful!');
                    Session::flash('m-class', 'alert-success');
                    return redirect()->route('invoice.index');
                } else {
                    Session::flash('message', 'Data Update Failed!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
            } else {
                Session::flash('message', 'Invoice Create Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
    }

    public function show($id)
    {
        if (Auth::user()->branchId != '') {
            $invoice = Invoice::with('client')->whereHas("client", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->find($id);
        } else {
            $invoice = Invoice::with('client')->whereHas("client", function ($query) {
                $query->where("resellerId", Auth::user()->resellerId);
            })->find($id);
        }

        if ($invoice != '') {
            $client = Client::with([
                'plan' => function ($query) {
                    $query->get();
                }
            ])->find($invoice->client->id);

            return view('admin.pages.invoice.show', compact('client', 'invoice'));
        } else {
            Session::flash('message', 'Invoice Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('invoice.index');
        }
    }

    public function edit($id)
    {
        if (Auth::user()->branchId != '') {
            $clients = Client::where('branchId', Auth::user()->branchId)->get();
            $invoice = Invoice::with('client')->whereHas("client", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->find($id);
        } else {
            $clients = Client::where('resellerId', Auth::user()->resellerId)->get();
            $invoice = Invoice::with('client')->whereHas("client", function ($query) {
                $query->where("resellerId", Auth::user()->resellerId);
            })->find($id);
        }
        if ($invoice != '') {

            if ($invoice->paid_amount > 0) {
                Session::flash('message', 'This Invoice Not Editable!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->route('invoice.index');
            } else {
                $accounts = \App\Account::where(function ($query) {
                    $query->where('branchId', Auth::user()->branchId)
                        ->where('resellerId', Auth::user()->resellerId);
                })
                    ->orwhere(function ($query) {
                        $query->whereNull('branchId')
                            ->where('resellerId', Auth::user()->resellerId)
                            ->where('account_type', '!=', 'Cash');
                    })->orderBY('id', 'ASC')->get();
                $reseller_has_extra_charge = Reseller::where('resellerId',Auth::user()->resellerId)
                    ->value('extra_charge');  // fetch only extra_charge column

                return view('admin.pages.invoice.edit', compact('clients', 'invoice', 'accounts','reseller_has_extra_charge'));
            }
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('invoice.index');
        }
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        $this->validate($request, [
            'plan_price' => 'numeric',
            'total' => 'numeric|min:1',
            'service_charge' => 'numeric|min:0',
            'discount' => 'required|numeric|lte:total|min:0',
            'sub_total' => 'numeric|min:0',
            'otc_charge' => 'numeric|min:0'
        ]);
        //        if (Auth::user()->resellerId != '') {
        //            $buyPrice = $request->buy_plan_price;
        //            $reseller = Reseller::find(Auth::user()->resellerId);
        //            $vatRate = $reseller->vat_rate;
        //            $reseller_data = [
        //                'due' => ($reseller->due - $invoice->buy_price) + $buyPrice
        //            ];
        //
        //            $rVatRate = Config::where('config_title', 'vatRate')->first();
        //
        //
        //            $rPrice = ceil(($request->buy_plan_price * 100) / (100 + $rVatRate->value));
        //            $r_vat = $buyPrice - $rPrice;
        //            $r_plan_price = $buyPrice - $r_vat;
        //            $r_invoice = ResellerInvoice::find($invoice->id);
        //            $reseller_invoice = [
        //                'resellerId' => Auth::user()->resellerId,
        //                'bill_month' => $request->bill_month,
        //                'bill_year' => $request->bill_year,
        //                'buy_price' => $r_plan_price,
        //                'total' => $r_plan_price,
        //                'vat' => $r_vat,
        //                'sub_total' => $request->buy_plan_price,
        //            ];
        //        } else {

        if (
            $request->otc_charge >0 &&
            \App\ClientPayment::where('client_id', $request->client_id_no)->exists()
        ) {
            $request->otc_charge=0.00;
        }
      //  dd( $request->client_id_no );
      //  dd( $request->otc_charge );
        $vatData = Config::where('config_title', 'vatRate')->first();
        $vatRate = $vatData->value;
        //            $buyPrice = 0;
        //        }

        if (Auth::user()->resellerId != '') {
            if ($request->sub_total <= 0) {
                Session::flash('message', 'You Can not give full discount!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
        $pre_due = $invoice->client->due;
        $all_total = $request->sub_total + $pre_due;
        $due = $all_total - $invoice->due;
        $client = Client::find($invoice->client_id);

        $update_client = [
            'due' => $due
        ];

        $price = ceil((($request->plan_price - $request->discount) * 100) / (100 + $vatRate));
        $vat = $request->plan_price - $price - $request->discount;
        $plan_price = $request->plan_price - $vat;
        $inputs = [
            'bandwidth' => $request->bandwidth,
            'bill_month' => $request->bill_month,
            'bill_year' => $request->bill_year,
            //            'buy_price' => $buyPrice,
            'plan_price' => $plan_price,
            'service_charge' => $request->service_charge,
            'otc_charge' => $request->otc_charge,
            'charge_for' => $request->charge_for,
            'total' => $request->total - $vat,
            'discount' => $request->discount,
            'all_total' => $request->sub_total - $vat,
            'vat' => $vat,
            'sub_total' => $request->sub_total,
            'paid_amount' => 0,
            'due' => $request->sub_total,
            'duration' => $invoice->client->plan->duration,
            'duration_unit' => $invoice->client->plan->duration_unit
        ];

        if ($invoice->update($inputs)) {
            //            if (Auth::user()->resellerId != '') {
            //                $reseller->update($reseller_data);
            //                $client->update($update_client);
            //                if (!empty($r_invoice)) {
            //                    $r_invoice->update($reseller_invoice);
            //                }
            //            } else {
            $client->update($update_client);
            //            }
            Session::flash('message', 'Data Update Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('invoice.index');
        } else {
            Session::flash('message', 'Data Update Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->branchId != '') {
            $invoice = Invoice::where("branchId", Auth::user()->branchId)->findOrFail($id);
        } else {
            $invoice = Invoice::where("resellerId", '=', Auth::user()->resellerId)->findOrFail($id);
        }

        DB::beginTransaction();
        try {
            if ($invoice != '') {
                if ($invoice->paid_amount > 0) {
                    throw new \Exception('This invoice was already paid!');
                } else {
                    $client = Client::find($invoice->client_id);
                    $c_data['due'] = $client->due - $invoice->due;
                    $client->update($c_data);
                    $invoice->delete();

                    DB::commit();
                    Session::flash('message', 'Invoice Deleted Successfully!');
                    Session::flash('m-class', 'alert-success');
                    return redirect()->back();
                }
            } else {
                throw new \Exception('Data Not Found!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('message', $e->getMessage());
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function restore($id)
    {
        if (Auth::user()->branchId != '') {
            $invoice = Invoice::onlyTrashed()->findOrFail($id);
        } else {
            $invoice = Invoice::onlyTrashed()->findOrFail($id);
        }

        DB::beginTransaction();
        try {
            if ($invoice != '') {
                $client = Client::find($invoice->client_id);
                $c_data['due'] = $client->due + $invoice->due;
                $client->update($c_data);
                $invoice->restore();

                DB::commit();
                Session::flash('message', 'Invoice Restored Successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->back();
            } else {
                throw new \Exception('Data Not Found!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('message', $e->getMessage());
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function trashInvoice(Request $request)
    {
        if (Auth::user()->branchId != '') {
            $invoiceData = Invoice::onlyTrashed()->whereHas("client")
                ->orderBy('created_at', 'DESC');
        } else {
            $invoiceData = Invoice::onlyTrashed()->whereHas("client")
                ->orderBy('created_at', 'DESC');
        }

        if ($request->from_date != '') {
            $invoiceData =  $invoiceData->whereBetween('created_at', $this->range);
        }
        $invoiceData = $invoiceData->get();

        $page_title = "All Deleted Invoice List";

        $admin = false;

        return view('admin.pages.invoice.index', compact('invoiceData', 'page_title', 'admin'));
    }

    public function paid($id)
    {
        if (Auth::user()->branchId != '') {
            $invoice = Invoice::with(['client'])->whereHas("client", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->find($id);
        } else {
            $invoice = Invoice::with(['client'])->whereHas("client", function ($query) {
                $query->where("resellerId", '=', Auth::user()->resellerId);
            })->find($id);
        }

        if ($invoice != '') {
            if ($invoice->due == 0) {
                Session::flash('message', 'This Invoice Already Paid!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->route('invoice.index');
            } else {
                if (Auth::user()->branchId != '') {
                    $accounts = Account::where('branchId', Auth::user()->branchId)->orderBY('id', 'ASC')->get();
                } else {
                    $accounts = Account::where('resellerId', Auth::user()->resellerId)->orderBY('id', 'ASC')->get();
                }
                return view('admin.pages.invoice.pay', compact('invoice', 'accounts'));
            }
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('invoice.index');
        }
    }

    public function paidUpdate(Request $request, $id)
    {
        $invoice = Invoice::with('client')->find($id);
        $client = Client::with([
            'plan' => function ($query) {
                $query->get();
            },
            'plan.server' => function ($query) {
                $query->get();
            }
        ])->find($invoice->client_id);

        $this->validate($request, [
            'due' => 'numeric',
            'paid_amount' => 'numeric',
            'pay_from_advance' => 'numeric',
        ]);

        if ($request->paid_amount > 0) {
            if ($request->paid_to == '') {
                Session::flash('message', 'Select Paid To!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
        if ($request->pay_from_advance > $client->balance) {
            return redirect()->back()->withErrors(['pay_from_advance' => 'Pay From Advance not more than ' . $client->balance . '!']);
        }
        if (($request->paid_amount + $request->pay_from_advance) == 0) {
            return redirect()->back()->withErrors([
                'paid_amount' => 'Please input Now Payment Amount or Pay From Advance',
                'pay_from_advance' => 'Please input Now Payment Amount or Pay From Advance'
            ]);
        }




        $invoice_paid_amount = $invoice->paid_amount;
        $invoice_due = $invoice->due;
        if ($invoice_paid_amount > 0) {
            $plan_price = 0.00;
            $service_charge = 0.00;
            $pre_due = $invoice->client->due;
            $vat = 0.00;
            $total = $pre_due;
            $discount = 0.00;
        } else {
            $plan_price = $invoice->plan_price;
            $service_charge = $invoice->service_charge;
            $pre_due = $invoice->client->due - $invoice_due;
            $vat = $invoice->vat;
            $total = $invoice->total + $pre_due;
            $discount = $invoice->discount;
        }


        $paid = $request->paid_amount + $request->pay_from_advance;
        if ($client->due >= $request->paid_amount) {
            $ad_payment = 0.00;
            if ($client->due >= $paid) {
                $due = $client->due - $paid;
            } else {
                $due = 0.00;
            }
        } else {
            $ad_payment = $request->paid_amount - $client->due;
            $due = 0.00;
        }

        $new_balance = ($client->balance + $ad_payment) - $request->pay_from_advance;
        $pre_balance = $client->balance;

        $update_client = [
            'due' => $due,
            'balance' => $new_balance
        ];

        if ($paid > $invoice->due) {
            $paid_amount = $invoice->due;
        } else {
            $paid_amount = $paid;
        }

        $inputs = [
            'paid_amount' => $invoice->paid_amount + $paid_amount,
            'due' => $invoice->due - $paid_amount
        ];

        $invoice_system = Config::where('config_title', 'invoice_system')->first()->value;
        if (Auth::user()->resellerId != '') {
            $reseller = Reseller::find(Auth::user()->resellerId);
            $vatRate = $reseller->vat_rate;
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

            $reseller_data = [];
            if ($invoice->paid_amount == 0) {
                if ($invoice->buy_price > $reseller->balance) {
                    Session::flash('message', 'You balance is low to pay invoice. Contact with upstream!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
                $reseller_data = [
                    'balance' => $reseller->balance - $invoice->buy_price
                ];
            }

            $update_client['status'] = 'On';
            if ($client->plan->type == 'PPPOE') {
                $clientData['profile'] = $client->plan->plan_name;
            } elseif ($client->plan->type == 'Hotspot') {
                $clientData['profile'] = $client->plan->plan_name;
            } else {
                $clientData['target'] = $client->client_ip;
            }
        } else {
            $vatData = Config::where('config_title', 'vatRate')->first();
            $vatRate = $vatData->value;
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
            $update_client['status'] = 'On';
            if ($client->plan->type == 'PPPOE') {
                $clientData['profile'] = $client->plan->plan_name;
            } elseif ($client->plan->type == 'Hotspot') {
                $clientData['profile'] = $client->plan->plan_name;
            } else {
                $clientData['target'] = $client->client_ip;
            }
        }

        $sub_total = ($total + $ad_payment) - $discount;

        $payment_inputs = [
            'client_id' => $invoice->client_id,
            'bandwidth' => $invoice->bandwidth,
            'bill_month' => $invoice->bill_month,
            'bill_year' => $invoice->bill_year,
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
            'pre_balance' => $pre_balance,
            'due' => $due,
            'user_id' => Auth::user()->id,
            'payment_date' => $request->payment_date,
            'branchId' => $client->branchId,
            'resellerId' => $client->resellerId
        ];

        $ac = \App\Account::find($request->paid_to);
        $account_balance = $ac->account_balance + $request->paid_amount + $request->charge;
        $ac_inputs = [
            'account_balance' => $account_balance
        ];

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
            'invoice_id' => $invoice->id,
            'account_id' => $request->paid_to,
            'tr_type' => 'Bill Payment',
            'tr_category' => 'Income',
            'tr_amount' => $request->paid_amount,
            'tr_vat' => $vat_paid,
            'charge' => $request->charge,
            'payer' => $client->client_name,
            'cr' => $request->paid_amount + $request->charge,
            'user_id' => Auth::user()->id,
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId,
            'trans_date' => $request->payment_date
        ];
        if ($request->exp_date == '') {
            $current_month = date('Y-m');
            $invoice_month_value = date_create($invoice->bill_year . '-' . $invoice->bill_month);
            $invoice_month = date_format($invoice_month_value, 'Y-m');

            $exp_time = explode(':', setting('exp_time'));

            if ($current_month == $invoice_month) {

                if ($invoice['duration_unit'] == '2') {
                    $date_exp = date("Y-m-d H:i:s", mktime($exp_time[0], $exp_time[1], '00', date("m"), date("d") + $invoice->duration, date("Y")));
                } else {
                    $date_exp = date("Y-m-d H:i:s", mktime($exp_time[0], $exp_time[1], '00', date("m") + $invoice->duration, date($c_exp_date), date("Y")));
                }
            } else {

                if ($invoice['duration_unit'] == '2') {
                    $date_exp = date("Y-m-d H:i:s", mktime($exp_time[0], $exp_time[1], '00', date($invoice->bill_month), date("d") + $invoice->duration, date($invoice->bill_year)));
                } else {
                    $date_exp = date("Y-m-d H:i:s", mktime($exp_time[0], $exp_time[1], '00', date($invoice->bill_month) + $invoice->duration, date($c_exp_date), date($invoice->bill_year)));
                }
            }

            $update_client['expiration'] = $date_exp;
        } else {
            $update_client['expiration'] = date($request->exp_date . " " . setting('exp_time') . ":00");
        }
        if ($client->start_transaction == "") {
            $update_client['start_transaction'] = date("Y-m-d");
        }

        DB::beginTransaction();
        try {
            if ($client->status == 'Off' && setting('using_mikrotik')) {
                $con = Roar::connect($client->plan->server->server_ip, $client->plan->server->server_port, $client->plan->server->username, encrypt_decrypt('decrypt', $client->plan->server->password));
                if ($con->isConnected()) {
                    if ($client->server_status == 2) {
                        $update_client['server_status'] = 1;
                        $update_client['server_active_date'] = date('Y-m-d');
                        if ($client->plan->type == 'PPPOE') {
                            $clientData = [
                                'name' => $client->username,
                                'service' => 'pppoe',
                                'password' => $client->server_password
                            ];
                            $addToServer = new Secret($con);
                            $addToServer->add($clientData);
                            $new_id = $addToServer->getId($client->username);
                        } elseif ($client->plan->type == 'Hotspot') {
                            $clientData = [
                                'name' => $client->username,
                                'password' => $client->server_password
                            ];
                            $addToServer = new HotspotUsers($con);
                            $addToServer->add($clientData);
                            $new_id = $addToServer->getId($client->username);
                        } else {
                            $b = \App\Bandwidth::find($client->plan->bandwidth_id);
                            ($b->rate_down_unit == 'Kbps') ? $unitdown = 'K' : $unitdown = 'M';
                            ($b->rate_up_unit == 'Kbps') ? $unitup = 'K' : $unitup = 'M';
                            $rate = $b->rate_up . $unitup . "/" . $b->rate_down . $unitdown;
                            $clientData = [
                                'name' => $client->client_name . " (" . $client->username . ")",
                                'target' => $client->client_ip,
                                'max-limit' => $rate
                            ];
                            $addToServer = new Simple($con);
                            $addToServer->add($clientData);
                            $new_id = $addToServer->getId($client->client_name . " (" . $client->username . ")");
                            $firewall_address = new FirewallAddressList($con);
                            if (Auth::user()->resellerId != '' && $reseller->exp_date > date('Y-m-d')) {
                                $fire_ip_data = [
                                    'address' => $client->client_ip,
                                    'list' => 'Redirect IP'
                                ];
                                $firewall_address->add($fire_ip_data);
                            }
                        }

                        if (!empty($new_id)) {

                            $tr_id = \App\Transaction::create($tr_inputs)->id;
                            if (!empty($tr_id)) {
                                $payment_inputs['tr_id'] = $tr_id;
                                if (\App\ClientPayment::create($payment_inputs)) {
                                    if ($client->update($update_client)) {
                                        $invoice->update($inputs);
                                        $ac->update($ac_inputs);
                                        if (Auth::user()->resellerId != '') {
                                            $reseller->update($reseller_data);
                                        }

                                        //========PAYMENT CONFIRMATION SMS=======
                                        $deelkoSMS = new DeelkoSMS();
                                        $deelkoSMS->sendSMS($client, 'sms_payment', $request->paid_amount);

                                        DB::commit();
                                        Session::flash('message', 'Invoice Paid Successful');
                                        Session::flash('m-class', 'alert-success');
                                        return redirect()->route('client.index');
                                    } else {
                                        DB::rollBack();
                                        Session::flash('message', 'Client Data Update Failed!');
                                        Session::flash('m-class', 'alert-danger');
                                        return redirect()->back();
                                    }
                                } else {
                                    DB::rollBack();
                                    Session::flash('message', 'Client Payment Create Failed!');
                                    Session::flash('m-class', 'alert-danger');
                                    return redirect()->back();
                                }
                            } else {
                                DB::rollBack();
                                Session::flash('message', 'Transaction Create Failed!');
                                Session::flash('m-class', 'alert-danger');
                                return redirect()->back();
                            }
                        } else {
                            DB::rollBack();
                            Session::flash('message', 'Data Create Failed To ISP Server!');
                            Session::flash('m-class', 'alert-danger');
                            return redirect()->back();
                        }
                    } else {
                        if ($client->plan->type == 'PPPOE') {
                            $updateToServer = new Secret($con);
                            $serverUserId = $updateToServer->getId($client->username);
                        } elseif ($client->plan->type == 'Hotspot') {
                            $updateToServer = new HotspotUsers($con);
                            $serverUserId = $updateToServer->getId($client->username);
                        } else {
                            $updateToServer = new Simple($con);
                            $serverUserId = $updateToServer->getId($client->client_name . " (" . $client->username . ")");
                        }
                        if (!empty($serverUserId)) {

                            $tr_id = \App\Transaction::create($tr_inputs)->id;
                            if (!empty($tr_id)) {
                                $payment_inputs['tr_id'] = $tr_id;
                                if (\App\ClientPayment::create($payment_inputs)) {
                                    if ($client->update($update_client)) {
                                        $invoice->update($inputs);
                                        $ac->update($ac_inputs);

                                        if (Auth::user()->resellerId != '') {
                                            $reseller->update($reseller_data);
                                        }

                                        $updateToServer->set($clientData, $serverUserId);
                                        if ($client->plan->type == 'IP') {
                                            $firewall_address = new FirewallAddressList($con);
                                            if (Auth::user()->resellerId != '') {
                                                if ($reseller->exp_date <= date('Y-m-d')) {
                                                    $get_f_id = $firewall_address->getId($client->client_ip);
                                                    $firewall_address->delete($get_f_id);
                                                }
                                            } else {
                                                $get_f_id = $firewall_address->getId($client->client_ip);
                                                $firewall_address->delete($get_f_id);
                                            }
                                        } else if ($client->plan->type == 'PPPOE') {
                                            $delActive = new Active($con);
                                            $ActiveId = $delActive->getId($client->username);
                                            $delActive->delete($ActiveId);
                                        } else {
                                            $delActive = new HotspotActive($con);
                                            $ActiveId = $delActive->getId($client->username);
                                            $delActive->delete($ActiveId);
                                        }

                                        //========PAYMENT CONFIRMATION SMS=======
                                        $deelkoSMS = new DeelkoSMS();
                                        $deelkoSMS->sendSMS($client, 'sms_payment', $request->paid_amount);

                                        DB::commit();
                                        Session::flash('message', 'Invoice Paid Successful');
                                        Session::flash('m-class', 'alert-success');
                                        return redirect()->route('invoice.due');
                                    } else {
                                        DB::rollBack();
                                        Session::flash('message', 'Client Data Update Failed!');
                                        Session::flash('m-class', 'alert-danger');
                                        return redirect()->back();
                                    }
                                } else {
                                    DB::rollBack();
                                    Session::flash('message', 'Client Payment Create Failed!');
                                    Session::flash('m-class', 'alert-danger');
                                    return redirect()->back();
                                }
                            } else {
                                DB::rollBack();
                                Session::flash('message', 'Transaction Create Failed!');
                                Session::flash('m-class', 'alert-danger');
                                return redirect()->back();
                            }
                        } else {
                            DB::rollBack();
                            Session::flash('message', 'Data Update Failed To ISP Server!');
                            Session::flash('m-class', 'alert-danger');
                            return redirect()->back();
                        }
                    }
                } else {
                    DB::rollBack();
                    Session::flash('message', 'Connect Failed To ISP Server!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
            } else {
                $tr_id = \App\Transaction::create($tr_inputs)->id;
                if (!empty($tr_id)) {
                    $payment_inputs['tr_id'] = $tr_id;
                    if (\App\ClientPayment::create($payment_inputs)) {
                        if ($client->update($update_client)) {
                            $invoice->update($inputs);
                            $ac->update($ac_inputs);
                            if (Auth::user()->resellerId != '') {
                                $reseller->update($reseller_data);
                            }
                            //========PAYMENT CONFIRMATION SMS=======
                            $deelkoSMS = new DeelkoSMS();
                            $deelkoSMS->sendSMS($client, 'sms_payment', $request->paid_amount);

                            DB::commit();
                            Session::flash('message', 'Invoice Paid Successful');
                            Session::flash('m-class', 'alert-success');
                            return redirect()->route('invoice.due');
                        } else {
                            DB::rollBack();
                            Session::flash('message', 'Client Data Update Failed!');
                            Session::flash('m-class', 'alert-danger');
                            return redirect()->back();
                        }
                    } else {
                        DB::rollBack();
                        Session::flash('message', 'Client Payment Create Failed!');
                        Session::flash('m-class', 'alert-danger');
                        return redirect()->back();
                    }
                } else {
                    DB::rollBack();
                    Session::flash('message', 'Transaction Create Failed!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('message', $e->getMessage() ?? 'Connection lost or session out');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function bulkInvoicePaid(Request $request)
    {
        if ($request->paid_to == '') {
            Session::flash('message', 'Select account to collect payment!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
        DB::beginTransaction();
        try {
            foreach ($request->invoiceNo as $id) {

                $invoice = Invoice::with('client')->find($id);
                $client = Client::with([
                    'plan' => function ($query) {
                        $query->get();
                    },
                    'plan.server' => function ($query) {
                        $query->get();
                    }
                ])->find($invoice->client_id);

                $invoice_paid_amount = $invoice->paid_amount;
                $invoice_due = $invoice->due;
                if ($invoice_paid_amount > 0) {
                    $plan_price = 0.00;
                    $service_charge = 0.00;
                    $pre_due = $invoice->client->due;
                    $vat = 0.00;
                    $total = $pre_due;
                    $discount = 0.00;
                } else {
                    $plan_price = $invoice->plan_price;
                    $service_charge = $invoice->service_charge;
                    $pre_due = $invoice->client->due - $invoice_due;
                    $vat = $invoice->vat;
                    $total = $invoice->total + $pre_due;
                    $discount = $invoice->discount;
                }

                //Manually defined
                $request->paid_amount = $invoice_due;
                $request->pay_from_advance = 0.00;
                $request->payment_date = date("Y-m-d");
                $request->charge = 0.00;

                $paid = $request->paid_amount + $request->pay_from_advance;
                if ($client->due >= $request->paid_amount) {
                    $ad_payment = 0.00;
                    if ($client->due >= $paid) {
                        $due = $client->due - $paid;
                    } else {
                        $due = 0.00;
                    }
                } else {
                    $ad_payment = $request->paid_amount - $client->due;
                    $due = 0.00;
                }

                $new_balance = ($client->balance + $ad_payment) - $request->pay_from_advance;
                $pre_balance = $client->balance;

                $update_client = [
                    'due' => $due,
                    'balance' => $new_balance
                ];

                if ($paid > $invoice->due) {
                    $paid_amount = $invoice->due;
                } else {
                    $paid_amount = $paid;
                }

                $inputs = [
                    'paid_amount' => $invoice->paid_amount + $paid_amount,
                    'due' => $invoice->due - $paid_amount
                ];

                $invoice_system = Config::where('config_title', 'invoice_system')->first()->value;

                if (Auth::user()->resellerId != '') {
                    $reseller = Reseller::find(Auth::user()->resellerId);
                    $vatRate = $reseller->vat_rate;
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


                    $reseller_data = [];
                    if ($invoice->paid_amount == 0) {
                        if ($invoice->buy_price > $reseller->balance) {
                            throw new \Exception('You balance is low to pay invoice. Contact with upstream!');
                            //                            Session::flash('message', 'You balance is low to pay invoice. Contact with upstream!');
                            //                            Session::flash('m-class', 'alert-danger');
                            //                            return redirect()->back();
                            break;
                        }
                        $reseller_data = [
                            'balance' => $reseller->balance - $invoice->buy_price
                        ];
                    }

                    $update_client['status'] = 'On';
                    if ($client->plan->type == 'PPPOE') {
                        $clientData['profile'] = $client->plan->plan_name;
                    } elseif ($client->plan->type == 'Hotspot') {
                        $clientData['profile'] = $client->plan->plan_name;
                    } else {
                        $clientData['target'] = $client->client_ip;
                    }
                } else {
                    $vatData = Config::where('config_title', 'vatRate')->first();
                    $vatRate = $vatData->value;
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
                    $update_client['status'] = 'On';
                    if ($client->plan->type == 'PPPOE') {
                        $clientData['profile'] = $client->plan->plan_name;
                    } elseif ($client->plan->type == 'Hotspot') {
                        $clientData['profile'] = $client->plan->plan_name;
                    } else {
                        $clientData['target'] = $client->client_ip;
                    }
                }

                $sub_total = ($total + $ad_payment) - $discount;

                $payment_inputs = [
                    'client_id' => $invoice->client_id,
                    'bandwidth' => $invoice->bandwidth,
                    'bill_month' => $invoice->bill_month,
                    'bill_year' => $invoice->bill_year,
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
                    'pre_balance' => $pre_balance,
                    'due' => $due,
                    'user_id' => Auth::user()->id,
                    'payment_date' => $request->payment_date,
                    'branchId' => $client->branchId,
                    'resellerId' => $client->resellerId
                ];

                $ac = \App\Account::find($request->paid_to);
                $account_balance = $ac->account_balance + $request->paid_amount;
                $ac_inputs = [
                    'account_balance' => $account_balance
                ];

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
                    'invoice_id' => $invoice->id,
                    'account_id' => $request->paid_to,
                    'tr_type' => 'Bill Payment',
                    'tr_category' => 'Income',
                    'tr_amount' => $request->paid_amount,
                    'tr_vat' => $vat_paid,
                    'charge' => 0,
                    'payer' => $client->client_name,
                    'cr' => $request->paid_amount,
                    'user_id' => Auth::user()->id,
                    'branchId' => Auth::user()->branchId,
                    'resellerId' => Auth::user()->resellerId,
                    'trans_date' => $request->payment_date
                ];

                $current_month = date('Y-m');
                $invoice_month_value = date_create($invoice->bill_year . '-' . $invoice->bill_month);
                $invoice_month = date_format($invoice_month_value, 'Y-m');

                $exp_time = explode(':', setting('exp_time'));
                if ($current_month == $invoice_month) {

                    if ($invoice['duration_unit'] == '2') {
                        $date_exp = date("Y-m-d H:i:s", mktime($exp_time[0], $exp_time[1], '00', date("m"), date("d") + $invoice->duration, date("Y")));
                    } else {
                        $date_exp = date("Y-m-d H:i:s", mktime($exp_time[0], $exp_time[1], '00', date("m") + $invoice->duration, date($c_exp_date), date("Y")));
                    }
                } else {

                    if ($invoice['duration_unit'] == '2') {
                        $date_exp = date("Y-m-d H:i:s", mktime($exp_time[0], $exp_time[1], '00', date($invoice->bill_month), date("d") + $invoice->duration, date($invoice->bill_year)));
                    } else {
                        $date_exp = date("Y-m-d H:i:s", mktime($exp_time[0], $exp_time[1], '00', date($invoice->bill_month) + $invoice->duration, date($c_exp_date), date($invoice->bill_year)));
                    }
                }

                $update_client['expiration'] = $date_exp;
                if ($client->start_transaction == "") {
                    $update_client['start_transaction'] = date("Y-m-d");
                }

                if ($client->status == 'Off' && setting('using_mikrotik')) {
                    $con = Roar::connect($client->plan->server->server_ip, $client->plan->server->server_port, $client->plan->server->username, encrypt_decrypt('decrypt', $client->plan->server->password));
                    if ($con->isConnected()) {
                        if ($client->server_status == 2) {
                            $update_client['server_status'] = 1;
                            $update_client['server_active_date'] = date('Y-m-d');
                            if ($client->plan->type == 'PPPOE') {
                                $clientData = [
                                    'name' => $client->username,
                                    'service' => 'pppoe',
                                    'password' => $client->server_password
                                ];
                                $addToServer = new Secret($con);
                                $addToServer->add($clientData);
                                $new_id = $addToServer->getId($client->username);
                            } elseif ($client->plan->type == 'Hotspot') {
                                $clientData = [
                                    'name' => $client->username,
                                    'password' => $client->server_password
                                ];
                                $addToServer = new HotspotUsers($con);
                                $addToServer->add($clientData);
                                $new_id = $addToServer->getId($client->username);
                            } else {
                                $b = \App\Bandwidth::find($client->plan->bandwidth_id);
                                ($b->rate_down_unit == 'Kbps') ? $unitdown = 'K' : $unitdown = 'M';
                                ($b->rate_up_unit == 'Kbps') ? $unitup = 'K' : $unitup = 'M';
                                $rate = $b->rate_up . $unitup . "/" . $b->rate_down . $unitdown;
                                $clientData = [
                                    'name' => $client->client_name . " (" . $client->username . ")",
                                    'target' => $client->client_ip,
                                    'max-limit' => $rate
                                ];
                                $addToServer = new Simple($con);
                                $addToServer->add($clientData);
                                $new_id = $addToServer->getId($client->client_name . " (" . $client->username . ")");
                                $firewall_address = new FirewallAddressList($con);
                                if (Auth::user()->resellerId != '' && $reseller->exp_date > date('Y-m-d')) {
                                    $fire_ip_data = [
                                        'address' => $client->client_ip,
                                        'list' => 'Redirect IP'
                                    ];
                                    $firewall_address->add($fire_ip_data);
                                }
                            }

                            if (!empty($new_id)) {

                                $tr_id = \App\Transaction::create($tr_inputs)->id;
                                if (!empty($tr_id)) {
                                    $payment_inputs['tr_id'] = $tr_id;
                                    if (\App\ClientPayment::create($payment_inputs)) {
                                        if ($client->update($update_client)) {
                                            $invoice->update($inputs);
                                            $ac->update($ac_inputs);
                                            if (Auth::user()->resellerId != '') {
                                                $reseller->update($reseller_data);
                                            }

                                            //========PAYMENT CONFIRMATION SMS=======
                                            $deelkoSMS = new DeelkoSMS();
                                            $deelkoSMS->sendSMS($client, 'sms_payment', $request->paid_amount);

                                            continue;
                                        }
                                    }
                                }
                            } else {
                                continue;
                            }
                        } else {
                            if ($client->plan->type == 'PPPOE') {
                                $updateToServer = new Secret($con);
                                $serverUserId = $updateToServer->getId($client->username);
                            } elseif ($client->plan->type == 'Hotspot') {
                                $updateToServer = new HotspotUsers($con);
                                $serverUserId = $updateToServer->getId($client->username);
                            } else {
                                $updateToServer = new Simple($con);
                                $serverUserId = $updateToServer->getId($client->client_name . " (" . $client->username . ")");
                            }
                            if (!empty($serverUserId)) {

                                $tr_id = \App\Transaction::create($tr_inputs)->id;
                                if (!empty($tr_id)) {
                                    $payment_inputs['tr_id'] = $tr_id;
                                    if (\App\ClientPayment::create($payment_inputs)) {
                                        if ($client->update($update_client)) {
                                            $invoice->update($inputs);
                                            $ac->update($ac_inputs);
                                            if (Auth::user()->resellerId != '') {
                                                $reseller->update($reseller_data);
                                            }

                                            $updateToServer->set($clientData, $serverUserId);
                                            if ($client->plan->type == 'IP') {
                                                $firewall_address = new FirewallAddressList($con);
                                                if (Auth::user()->resellerId != '') {
                                                    if ($reseller->exp_date <= date('Y-m-d')) {
                                                        $get_f_id = $firewall_address->getId($client->client_ip);
                                                        $firewall_address->delete($get_f_id);
                                                    }
                                                } else {
                                                    $get_f_id = $firewall_address->getId($client->client_ip);
                                                    $firewall_address->delete($get_f_id);
                                                }
                                            } else if ($client->plan->type == 'PPPOE') {
                                                $delActive = new Active($con);
                                                $ActiveId = $delActive->getId($client->username);
                                                $delActive->delete($ActiveId);
                                            } else {
                                                $delActive = new HotspotActive($con);
                                                $ActiveId = $delActive->getId($client->username);
                                                $delActive->delete($ActiveId);
                                            }

                                            //========PAYMENT CONFIRMATION SMS=======
                                            $deelkoSMS = new DeelkoSMS();
                                            $deelkoSMS->sendSMS($client, 'sms_payment', $request->paid_amount);

                                            continue;
                                        }
                                    }
                                }
                            } else {
                                continue;
                            }
                        }
                    } else {
                        continue;
                    }
                } else {
                    $tr_id = \App\Transaction::create($tr_inputs)->id;
                    if (!empty($tr_id)) {
                        $payment_inputs['tr_id'] = $tr_id;
                        if (\App\ClientPayment::create($payment_inputs)) {
                            if ($client->update($update_client)) {
                                $invoice->update($inputs);
                                $ac->update($ac_inputs);
                                if (Auth::user()->resellerId != '') {
                                    $reseller->update($reseller_data);
                                }
                                //========PAYMENT CONFIRMATION SMS=======
                                $deelkoSMS = new DeelkoSMS();
                                $deelkoSMS->sendSMS($client, 'sms_payment', $request->paid_amount);
                                continue;
                            }
                        }
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('message', $e->getMessage() ?? 'Connection lost or session out');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
        Session::flash('message', 'Payment Collection Successful!');
        Session::flash('m-class', 'alert-success');
        return redirect()->back();
    }

    public function resellerClientInvoiceList(Request $request)
    {
        $auth_user = Auth::user();
        $resellerId = ($auth_user->resellerId != '') ? $auth_user->resellerId : $request->resellerId;
        $resellers = Reseller::get();
        if ($resellerId != '') {
            $invoiceData =  Invoice::with(['client'])->whereHas("client")
                ->where('resellerId', $resellerId)->where('paid_amount', '>', 0);
        } else {
            $invoiceData = Invoice::with(['client'])->whereHas("client")
                ->whereNotNull('resellerId')->where('paid_amount', '>', 0);
        }
        if ($request->from_date != '') {
            $invoiceData = $invoiceData->whereBetween('created_at', $this->range);
        }
        $invoiceData = $invoiceData->whereBetween('created_at', $this->range)->get();


        $page_title = "Reseller Paid Invoice List";
        $admin = false;
        $route_url = 'reseller.client.invoice';

        return view(
            'admin.pages.reseller_client_invoice_list',
            compact('invoiceData', 'resellers', 'route_url', 'page_title', 'admin')
        );
    }

    public function bulkInvoicePrint(Request $request)
    {
        if (Auth::user()->branchId != '') {
            $invoices = Invoice::with('client')->whereHas("client", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->whereIn('id', $request->invoiceNo)->get();
        } else {
            $invoices = Invoice::with('client')->whereHas("client", function ($query) {
                $query->where("resellerId", Auth::user()->resellerId);
            })->whereIn('id', $request->invoiceNo)->get();
        }

        if (!empty($invoices)) {

            $clientIds = $invoices->pluck('client_id');
            $invoicesWithDue = Invoice::whereIn('client_id', $clientIds)
                ->where('due', '>', 0)
                ->get();

            // Group invoices by client_id
            $groupedInvoices = $invoicesWithDue->groupBy('client_id');

            return view('admin.pages.invoice.print_all_due_bulk', compact('groupedInvoices'));
        } else {
            Session::flash('message', 'No invoice was selected!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    //     public function bulkInvoicePrint(Request $request)
    // {
    //     if (Auth::user()->branchId != '') {
    //         // Fetch invoices for the authenticated user's branch
    //         $invoices = Invoice::with('client')
    //             ->whereHas("client", function ($query) {
    //                 $query->where("branchId", Auth::user()->branchId);
    //             })
    //             ->whereIn('id', $request->invoiceNo)
    //             ->get();
    //     } else {
    //         // Fetch invoices for the authenticated user's reseller
    //         $invoices = Invoice::with('client')
    //             ->whereHas("client", function ($query) {
    //                 $query->where("resellerId", Auth::user()->resellerId);
    //             })
    //             ->whereIn('id', $request->invoiceNo)
    //             ->get();
    //     }

    //     if ($invoices->isNotEmpty()) {
    //         // Group invoices by client_id
    //         $groupedInvoices = $invoices->groupBy('client_id');

    //         // Initialize arrays to store totals for each client
    //         $totalVAT = [];
    //         $totalDue = [];

    //         // Calculate total VAT and Due for each client
    //         foreach ($groupedInvoices as $clientId => $clientInvoices) {
    //             $totalVAT[$clientId] = $clientInvoices->sum('vat');
    //             $totalDue[$clientId] = $clientInvoices->sum('due');
    //         }

    //         // Fetch one invoice for view reference
    //         $invoice = Invoice::first(); 

    //         return view('admin.pages.invoice.print_all_due_bulk', compact('invoices', 'invoice', 'totalVAT', 'totalDue'));
    //     } else {
    //         Session::flash('message', 'No invoice was selected!');
    //         Session::flash('m-class', 'alert-danger');
    //         return redirect()->back();
    //     }
    // }


    public function deleteBulkInvoice(Request $request)
    {
        $invoiceIds = $request->input('invoice_ids');

        if (is_array($invoiceIds) && count($invoiceIds) > 0) {
            // Permanently delete the invoices with the specified IDs
            Invoice::whereIn('id', $invoiceIds)->forceDelete();

            // Convert the array of IDs into a comma-separated string
            $invoiceIdsString = implode(', ', $invoiceIds);

            // Set a flash message with the invoice IDs
            $request->session()->flash('success', "{$invoiceIdsString} Invoices have been permanently deleted successfully.");

            // After deleting the invoice fix the client profile
            DB::statement("
           UPDATE clients 
                SET due = (
                    SELECT COALESCE(SUM(invoices.due), 0) 
                    FROM invoices 
                    WHERE invoices.client_id = clients.id 
                    AND invoices.deleted_at IS NULL
                    AND invoices.due > 0 
                )
                WHERE clients.deleted_at IS NULL;
        ");

            return response()->json([
                "Message" => "Selected invoices have been permanently deleted successfully."
            ]);
        } else {
            return response()->json([
                "Message" => "No invoices selected for deletion."
            ], 400);
        }
    }

    public function trushBulkInvoice(Request $request)
    {
        $invoiceIds = $request->input('invoice_ids');
        if (is_array($invoiceIds) && count($invoiceIds) > 0) {
            // Permanently delete the invoices with the specified IDs
            Invoice::whereIn('id', $invoiceIds)->delete();

            // Convert the array of IDs into a comma-separated string
            $invoiceIdsString = implode(', ', $invoiceIds);

            // Set a flash message with the invoice IDs
            $request->session()->flash('success', "{$invoiceIdsString} Invoices have been move to trashed successfully.");

            // After deleting the invoice fix the client profile
            DB::statement("
          UPDATE clients 
                SET due = (
                    SELECT COALESCE(SUM(invoices.due), 0) 
                    FROM invoices 
                    WHERE invoices.client_id = clients.id 
                    AND invoices.deleted_at IS NULL
                    AND invoices.due > 0 
                )
                WHERE clients.deleted_at IS NULL;
        ");

            return response()->json([
                "Message" => "Selected invoices have been move to trashed successfully."
            ]);
        } else {
            return response()->json([
                "Message" => "No invoices selected for trash."
            ], 400);
        }
    }

    public function all_In_OneInvoicesListPrint(Request $request, $id)
    {
        $client = Client::find($id);
        $invoices = Invoice::where('client_id', $id)->where('due', '>', 0)->get(); // Fetch all invoices
        $invoice = Invoice::where('client_id', $id)->first(); // Fetch one invoice

        // Calculate totals of vat and due
        $totalVAT = $invoices->sum('vat');
        $totalDue = $invoices->sum('due');

        return view('admin.pages.invoice.all_show', compact('client', 'invoices', 'invoice', 'totalVAT', 'totalDue'));
    }
}
