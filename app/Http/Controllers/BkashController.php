<?php

namespace App\Http\Controllers;

use App\Account;
use App\ClientPayment;
use App\Config;
use App\Invoice;
use App\Mikrotik;
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

class BkashController extends Controller
{
    public function getToken(Request $request)
    {
//        return 'ok';
        $bKash = $this->bKashConfig();
        session()->forget('bkash_token');

        $post_token = array(
            'app_key' => $bKash->app_key,
            'app_secret' => $bKash->app_secret
        );

        $url = curl_init("$bKash->base_url/checkout/token/grant");
        $post_token = json_encode($post_token);
        $header = array(
            'Content-Type:application/json',
            "password:$bKash->password",
            "username:$bKash->username"
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $post_token);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        $resultdata = curl_exec($url);
        curl_close($url);

//        //GRANT TOKEN ==== GENERATE BKASH REPORT
//        $_bkashFileName = base_path().'/bkashreport.doc';
//        $_bkashReport = "\n\n\n============== GRANT TOKEN ==============\n".$resultdata;
//        file_put_contents($_bkashFileName, $_bkashReport, FILE_APPEND | LOCK_EX);

        $response = json_decode($resultdata, true);

        if (array_key_exists('msg', $response)) {
            return $response;
        }

        session()->put('bkash_token', $response['id_token']);

        return response()->json(['success', true]);
    }

    public function createPayment(Request $request)
    {

//        if (Session::get('payment_start') != 'On') {
//
//            return response()->json([
//                'errorMessage' => 'Unauthorised',
//                'errorCode' => 2070
//            ], 422);
//
//        }
        $bKash = $this->bKashConfig();

        $payer_id = Session::get('payer_id');

//        Session::put('account_type', 'Mobile Bank');
//        Session::put('account_name', 'bKash Account');


        if (Session::get('payer_type') == 'client') {
            if (((string)$request->amount != (string)session()->get('invoice_amount'))) {
                return response()->json([
                    'errorMessage' => 'Amount Mismatch',
                    'errorCode' => 2006
                ], 422);
            }
            $invoice_id = "CINV" . $payer_id . date("YmdHs");//must be unique
        } else {
            Session::put('invoice_amount', $request->amount);
            $invoice_id = "RINV" . $payer_id . date("YmdHs");//must be unique
        }

        $token = session()->get('bkash_token');

        $request['intent'] = 'sale';
        $request['currency'] = 'BDT';
        $request['merchantInvoiceNumber'] = $invoice_id;

        $url = curl_init("$bKash->base_url/checkout/payment/create");
        $request_data_json = json_encode($request->all());
        $header = array(
            'Content-Type:application/json',
            "authorization: $token",
            "x-app-key: $bKash->app_key"
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $request_data_json);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        curl_close($url);
//        $object = json_decode($resultdata);
//        $object->orgLogo = "https://" . \Request::getHttpHost() . "/assets/images/" . $bKash->logo;
//        $json = json_encode($object);
//        Session::forget('payment_start');


//        //CREATE PAYMENT ==== GENERATE BKASH REPORT
//        $_bkashFileName = base_path().'/bkashreport.doc';
//        $_bkashReport = "\n\n\n============== CREATE PAYMENT ==============\n".$resultdata;
//        file_put_contents($_bkashFileName, $_bkashReport, FILE_APPEND | LOCK_EX);

        return json_decode($resultdata, true);;
    }

    public function executePayment(Request $request)
    {
        $token = session()->get('bkash_token');
        $bKash = $this->bKashConfig();

        $paymentID = $request->paymentID;
        $url = curl_init("$bKash->base_url/checkout/payment/execute/" . $paymentID);
        $header = array(
            'Content-Type:application/json',
            "authorization:$token",
            "x-app-key:$bKash->app_key"
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url,CURLOPT_TIMEOUT,30);
        $resultdata = curl_exec($url);
        curl_close($url);

//        //EXECUTE PAYMENT ==== GENERATE BKASH REPORT
//        $_bkashFileName = base_path().'/bkashreport.doc';
//        $_bkashReport = "\n\n\n============== EXECUTE PAYMENT ==============\n".$resultdata;
//        file_put_contents($_bkashFileName, $_bkashReport, FILE_APPEND | LOCK_EX);
//////        return 'ok';

        return json_decode($resultdata, true);
    }

    public function queryPayment(Request $request)
    {
        $token = session()->get('bkash_token');
        $bKash = $this->bKashConfig();
        $paymentID = $request->payment_info['payment_id'];

        $url = curl_init("$bKash->base_url/checkout/payment/query/" . $paymentID);
        $header = array(
            'Content-Type:application/json',
            "authorization:$token",
            "x-app-key:$bKash->app_key"
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        $resultdata = curl_exec($url);
        curl_close($url);


//        //QUERY PAYMENT ==== GENERATE BKASH REPORT
//        $_bkashFileName = base_path().'/bkashreport.doc';
//        $_bkashReport = "\n\n\n============== QUERY PAYMENT ==============\n\n TOKEN: $token\n\n".$resultdata;
//        file_put_contents($_bkashFileName, $_bkashReport, FILE_APPEND | LOCK_EX);

        return json_decode($resultdata, true);
    }

    public function searchTrans(Request $request)
    {
        $token = session()->get('bkash_token');
        $bKash = $this->bKashConfig();
        if(empty($token)){
            $post_token = array(
                'app_key' => $bKash->app_key,
                'app_secret' => $bKash->app_secret
            );

            $url = curl_init("$bKash->base_url/checkout/token/grant");
            $post_token = json_encode($post_token);
            $header = array(
                'Content-Type:application/json',
                "password:$bKash->password",
                "username:$bKash->username"
            );

            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url, CURLOPT_POSTFIELDS, $post_token);
            curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
            $resultdata = curl_exec($url);
            curl_close($url);

//            //GRANT TOKEN ==== GENERATE BKASH REPORT
//            $_bkashFileName = base_path().'/bkashreport.doc';
//            $_bkashReport = "\n\n\n============== SEARCH TRANSACTION GRANT TOKEN ==============\n".$resultdata;
//            file_put_contents($_bkashFileName, $_bkashReport, FILE_APPEND | LOCK_EX);

            $response = json_decode($resultdata, true);
            session()->put('bkash_token', $response['id_token']);

            $token = $response['id_token'];
        }


        $transaction = Transaction::find($request->id);
        $trxID = $transaction->bkash_trxID;
        $url = curl_init("$bKash->base_url/checkout/payment/search/" . $trxID);
        $header = array(
            'Content-Type:application/json',
            "authorization:$token",
            "x-app-key:$bKash->app_key"
        );
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($url, CURLOPT_TIMEOUT, 30);
        $resultdata = curl_exec($url);
        curl_close($url);

//        //GENERATE BKASH REPORT
//        $_bkashFileName = base_path().'/bkashreport.doc';
//        $_bkashReport = "\n\n\n============== SEARCH TRANSACTION REPORT ==============\n".$resultdata;
//        file_put_contents($_bkashFileName, $_bkashReport, FILE_APPEND | LOCK_EX);

        return response()->json($resultdata);
    }

    public function bkashSuccess(Request $request)
    {
        if ($request->isMethod('post')) {

            $payment = new BkashPayment();

            if (Session::get('payer_type') == 'client') {
                $payment->clientPayment(Session::get('payer_id'), session()->get('invoice_amount'),$request->payment_info);
            } else {
                $payment->resellerRecharge(Session::get('payer_id'), session()->get('invoice_amount'),$request->payment_info);
            }
            return response()->json(['status' => true]);

        } else {
            Session::flash('message', 'You Have Not Permission To View This Page!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function setSession()
    {
        Session::flash('message', 'Please Try Again!');
        Session::flash('m-class', 'alert-danger');
        return 'Done';
    }

    public function bKashConfig()
    {
        // You can import it from your Database
        $bkash_app_key = \App\Config::where('config_title', 'bkash_app_key')->first()->value;
        $bkash_app_secret = \App\Config::where('config_title', 'bkash_app_secret')->first()->value;
        $bkash_username = \App\Config::where('config_title', 'bkash_username')->first()->value;
        $bkash_password = \App\Config::where('config_title', 'bkash_password')->first()->value;
        $bkash_base_url = \App\Config::where('config_title', 'bkash_pr_root_url')->first()->value;


//        $bkash_app_key = '5nej5keguopj928ekcj3dne8p'; // bKash Merchant API APP KEY
//        $bkash_app_secret = '1honf6u1c56mqcivtc9ffl960slp4v2756jle5925nbooa46ch62'; // bKash Merchant API APP SECRET
//        $bkash_username = 'testdemo'; // bKash Merchant API USERNAME
//        $bkash_password = 'test%#de23@msdao'; // bKash Merchant API PASSWORD
//        $bkash_base_url = 'https://checkout.sandbox.bka.sh/v1.2.0-beta'; // For Live Production URL: https://checkout.pay.bka.sh/v1.2.0-beta


        $config = [
            'app_key' => $bkash_app_key,
            'app_secret' => $bkash_app_secret,
            'username' => $bkash_username,
            'password' => $bkash_password,
            'base_url' => $bkash_base_url
        ];

        return (object)$config;
    }


    //Bill Payment API Integration
    public function queryBill(Request $request)
    {
        //Validate bKash request data
        $validator = Validator::make($request->all(), [
            'UserName' => 'required',
            'Password' => 'required',
            'UserId' => 'required',
//            'BillMonth' => 'required',
            'Amount' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ErrorCode' => "406",
                'ErrorMsg' => 'Mandatory Field missing'
            ]);
        }

        //Verifying API Username & Password
        if ($request->UserName != config('api.bKash.UserName')  ||
            !Hash::check($request->Password, config('api.bKash.Password'))) {

            return response([
                'ErrorCode' => "403",
                'ErrorMsg' => 'Authentication failed'
            ]);
        }

        //Find Client Info
        $client = Client::where('username', $request->UserId)
            ->where('server_status',1)
            ->first();

        //If CLient record found
        if($client != ''){

            if($client->due <= 0){
                return response([
                    'ErrorCode' => "436",
                    'ErrorMsg' => 'Already paid'
                ]);
            }

            $response = [
                'ErrorCode' => "200",
                'ErrorMsg' => 'Successful'
            ];

            $response['BillAmount'] = $client->due;
            $response['BillDueDate'] = date('Ymd', strtotime($client->expiration));
            $response['QueryTime'] = date('YmdHis');

            return response()->json($response);
        } else {
            return response()->json([
                'ErrorCode' => "404",
                'ErrorMsg' => 'Client not found'
            ]);
        }

    }


    //Bill Payment API Intregation
    public function payBill(Request $request)
    {
        //Validate bKash request data
        $validator = Validator::make($request->all(), [
            'UserName' => 'required',
            'Password' => 'required',
            'UserId' => 'required',
//            'BillMonth' => 'required',
            'Amount' => 'required',
            'UserMobileNumber' => 'nullable',
            'TrxId' => 'required',
            'PayTime' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ErrorCode' => "406",
                'ErrorMsg' => 'Mandatory Field missing'
            ]);
        }

        //Verifying API Username & Password
        if ($request->UserName != config('api.bKash.UserName')  ||
            !Hash::check($request->Password, config('api.bKash.Password'))) {

            return response([
                'ErrorCode' => "403",
                'ErrorMsg' => 'Authentication failed'
            ]);
        }

        $client = Client::with([
            'plan' => function ($query) {
                $query->get();
            },
            'plan.server' => function ($query) {
                $query->get();
            }
        ])->where('username', $request->UserId)
        ->where('server_status', 1)->first();

        if($client == ''){
            return response()->json([
            'ErrorCode' => "404",
            'ErrorMsg' => 'Client not found'
            ]);
        }


        if($client->due <= 0){
            return response([
                'ErrorCode' => "436",
                'ErrorMsg' => 'Already paid'
            ]);
        }
        
        $bkash_account_exists = Account::where('resellerId', $client->resellerId)->where('account_type', 'bKash')->count();
        if(!$bkash_account_exists) {
            Account::create([
                'account_name' => 'bKash',
                'account_type' => 'bKash',
                'account_number' => NULL,
                'account_balance' => 0,
                'resellerId' => $client->resellerId,
            ]);
        }

        if ($request->Amount != $client->due) {
            return response()->json([
                'ErrorCode' => "439",
                'ErrorMsg' => 'Pay amount and biller amount not match'
            ]);
        }

        $paid = $request->Amount;
        if ($client->due >= $request->Amount) {
            $ad_payment = 0.00;
            $due = $client->due - $request->Amount;
        } else {
            $ad_payment = $request->Amount - $client->due;
            $due = 0.00;
        }

        $new_balance = $client->balance + $ad_payment;

        $update_client = [
            'due' => $due,
            'balance' => $new_balance
        ];

        //GET LATEST DUE INVOICE
        $invoice = Invoice::where('client_id', $client->id)->orderBy('bill_year', 'DESC')->orderBy('bill_month', 'DESC')->where('due', '>', 0)->first();
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
        }
        else {
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
            'client_id' => $client->id,
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
            'new_paid' => $request->Amount,
            'paid_from_advance' => 0,
            'pre_balance' => $client->balance,
            'due' => $due,
            'user_id' => 1,
            'payment_date' => date('Y-m-d'),
            'branchId' => $client->branchId,
            'resellerId' => $client->resellerId
        ];

        $ac = Account::where('resellerId', $client->resellerId)
            ->where('account_type', 'bKash')->first();;
        $account_balance = $ac->account_balance + $request->Amount;
        $ac_inputs = [
            'account_balance' => $account_balance
        ];

        $update_client['status'] = 'On';

        if ($client->resellerId != '') {
            $reseller = Reseller::find($client->resellerId);
            $vatRate = $reseller->vat_rate;
        }
        else {
            $vatData = Config::where('config_title', 'vatRate')->first();
            $vatRate = $vatData->value;
        }

        if ($vat > 0) {
            if ($request->Amount > $service_charge) {
                $p_price_paid = $request->Amount - $service_charge;
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
            'account_id' => $ac->id,
            'tr_type' => 'Bill Payment',
            'tr_category' => 'Income',
            'tr_amount' => $request->Amount,
            'tr_vat' => $vat_paid,
            'payer' => $client->client_name,
            'cr' => $request->Amount,
            'bkash_trxID' => $request->TrxId,
            'user_id' => 1,
            'branchId' => $client->branchId,
            'resellerId' => $client->resellerId,
            'trans_date' => date('Y-m-d'),
            'PayTime' => $request->PayTime
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
                            $deelkoSMS->sendSMS($client, 'sms_payment', $request->Amount);

                            DB::commit();
                            $response = [
                                'ErrorCode' => "200",
                                'ErrorMsg' => 'Successful'
                            ];

                            $response['ConsumerName'] = $client->client_name;
                            $response['TotalAmount'] =  $request->Amount;
                            $response['TrxId'] = $request->TrxId;
                            $response['MiddlewarePayTime'] = $request->PayTime;

                            return response()->json($response);

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
                $dueInvoices = Invoice::where('client_id', $client->id)
                    ->where('due', '>', 0)
                    ->orderBy('bill_year', 'ASC')
                    ->orderBy('bill_month', 'ASC')->get();

                $_updated_invoices = [];
                //LOOPING OVER ALL DUE INVOICES
                foreach ($dueInvoices as $singleInvoice) {
                    //COUNTING DUE INVOICE
                    $invoiceCount = Invoice::where('client_id', $client->id)
                        ->where('due', '!=', 0)->count();

                    if ($paid > $singleInvoice['due']) {
                        $paid_amount = $singleInvoice['due'];
                    } else {
                        $paid_amount = $paid;
                    }

                    $inputs = [
                        'paid_amount' => $singleInvoice['paid_amount'] + $paid_amount,
                        'due' => $singleInvoice['due'] - $paid_amount
                    ];


                    //DETERMINE EXPIRE DATE OF MONTH
                    $invoice_system = Config::where('config_title', 'invoice_system')->first()->value;
                    if ($client->resellerId != '') {
                        $reseller = Reseller::find($client->resellerId);
                        if($invoice_system == 'fixed') {
                            $c_exp_date = $reseller->c_exp_date;
                        } else {
                            $isExpired = strtotime(now())-strtotime($client->expiration);
                            if($isExpired >= 0){
                                $c_exp_date = date('d');
                            }else{
                                $c_exp_date = date('d',strtotime($client->expiration));
                            }
                        }
                    }
                    else {
                        $c_exp_value = Config::where('config_title', 'exp_date')->first();
                        if($invoice_system == 'fixed') {
                            $c_exp_date = $c_exp_value->value;
                        } else {
                            $isExpired = strtotime(now())-strtotime($client->expiration);
                            if($isExpired >= 0){
                                $c_exp_date = date('d');
                            }else{
                                $c_exp_date = date('d',strtotime($client->expiration));
                            }
                        }
                    }

                    $exp_time = explode(':',setting('exp_time'));
                    $date_exp = date("Y-m-d H:i:s", mktime(date($exp_time[0]), date($exp_time[1]), date("00"), date("m") + $singleInvoice->duration, date($c_exp_date), date("Y")));

                    $update_client['expiration'] = $date_exp;


                    $inv = Invoice::find($singleInvoice['id']);

                    //STORING DATA FOR INVOICE ROLLBACK
                    if($invoiceCount > 1){
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

                                    DB::commit();
                                    $response = [
                                        'ErrorCode' => "200",
                                        'ErrorMsg' => 'Successful'
                                    ];

                                    $response['ConsumerName'] = $client->client_name;
                                    $response['TotalAmount'] =  $request->Amount;
                                    $response['TrxId'] = $request->TrxId;
                                    $response['MiddlewarePayTime'] = $request->PayTime;

                                    return response()->json($response);
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
        } catch(\Throwable $e){
            DB::rollBack();
            if(!empty($_updated_invoices)){
                foreach ($_updated_invoices as $key => $u_invoice) {
                    $_inv = Invoice::find($key);
                    $_inv->update($u_invoice);
                }
            }

            return response([
                'ErrorCode' => "435",
                'ErrorMsg' => 'Data Mismatch'
            ]);
        }
    }

    public function searchTransaction(Request $request)
    {
        //Validate bKash request data
        $validator = Validator::make($request->all(), [
            'UserName' => 'required',
            'Password' => 'required',
            'TrxId' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ErrorCode' => "406",
                'ErrorMsg' => 'Mandatory Field missing'
            ]);
        }

        //Verifying API Username & Password
        if ($request->UserName != config('api.bKash.UserName')  ||
            !Hash::check($request->Password, config('api.bKash.Password'))) {

            return response([
                'ErrorCode' => "403",
                'ErrorMsg' => 'Authentication failed'
            ]);
        }

        $transaction = Transaction::where('bkash_trxID', $request->TrxId)->first();

        if($transaction != ''){

            $response = [
                'ErrorCode' => "200",
                'ErrorMsg' => 'Successful'
            ];

            $response['TotalAmount'] = $transaction->tr_amount;
            $response['TrxId'] = $transaction->bkash_trxID;
            $response['MiddlewarePayTime'] = $transaction->PayTime;

            return response()->json($response);
        } else {
            return response()->json([
                'ErrorCode' => "404",
                'ErrorMsg' => 'Transaction not found'
            ]);
        }

    }
}
