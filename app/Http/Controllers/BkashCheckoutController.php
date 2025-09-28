<?php

namespace App\Http\Controllers;

use App\BkashCheckoutSetting;
use App\Client;
use App\User;
use App\Invoice;
use App\Reseller;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BkashCheckoutController extends Controller
{
   public function update_checkout_settings(Request $request)
{
    // Validation
    $validatedData = $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
        'app_key' => 'required|string',
        'app_secret' => 'required|string',
        'grant_token_api_endpoint' => 'required|string',
        'create_payment_api_endpoint' => 'required|string',
        'execute_payment_api_endpoint' => 'required|string',
        'query_payment_api_endpoint' => 'required|string',
        'resellerId' => 'nullable'
    ]);

    // Determine the target records based on resellerId
    $resellerId = $validatedData['resellerId'] ?? null;

    // Delete only relevant records
    BkashCheckoutSetting::where('resellerId', $resellerId)->delete();

    // Store the validated data using Eloquent
    $settings = BkashCheckoutSetting::updateOrCreate(
        [
            'username' => $validatedData['username'],
            'resellerId' => $resellerId, // Ensure uniqueness by both username and resellerId
        ],
        [
            'password' => $validatedData['password'],
            'app_key' => $validatedData['app_key'],
            'app_secret' => $validatedData['app_secret'],
            'grant_token_api_endpoint' => $validatedData['grant_token_api_endpoint'],
            'create_payment_api_endpoint' => $validatedData['create_payment_api_endpoint'],
            'execute_payment_api_endpoint' => $validatedData['execute_payment_api_endpoint'],
            'query_payment_api_endpoint' => $validatedData['query_payment_api_endpoint'],
        ]
    );

    return back()->with('success', 'Checkout settings updated successfully!');
}


    public function genarate_grant_token(Request $request)
    {
        $validatedData = $request->validate([
            'recharge_amount' => 'nullable|numeric',
            'resellerId' => 'nullable|numeric'
        ]);

        $bkashSetting = null;
        $recharge_amount = $request->recharge_amount;
        //  dd($request->resellerId);

        // dd($request->recharge_amount , $request->resellerId);

        if (Auth::user() != null && Auth::user()->resellerId) {
            $bkashSetting = BkashCheckoutSetting::where('resellerId', Auth::user()->resellerId)->latest()->first();
        } elseif ($request->recharge_amount && $request->resellerId) {
            $bkashSetting = BkashCheckoutSetting::whereNull('resellerId')->latest()->first();
           
        } else {
            $bkashSetting = BkashCheckoutSetting::whereNull('resellerId')->latest()->first();
        }

        $requestHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'username' => $bkashSetting->username,
            'password' => $bkashSetting->password
        ];

        $requestBody = [
            'app_key' => $bkashSetting->app_key,
            'app_secret' => $bkashSetting->app_secret
        ];

        $grantTokenAPI = $bkashSetting->grant_token_api_endpoint;
        
        $tokenGenarateRequest = null;

        if ($bkashSetting && $requestHeaders && $requestBody) {
            $tokenGenarateRequest = Http::withHeaders($requestHeaders)->post($grantTokenAPI, $requestBody)->json();
            // dd($tokenGenarateRequest);
        } else {
            return back()->with('error', 'Bkash Setting Not Correctly set!');
        }

        // check success with id_token is available
        $id_token = "";

        if ($tokenGenarateRequest['statusCode'] == "0000" && $tokenGenarateRequest['statusMessage'] === "Successful" && $tokenGenarateRequest['id_token'] != '') {
            $id_token = $tokenGenarateRequest['id_token'];
         } else {
            return back()->with('error', 'Bkash Setting Not Correctly set!');
        }

        //save the token in clients table for further process
        $client_username = Auth::user()->username ?? 'reseller';
        if ($client_username != 'reseller') {
            // Find the client by username
            $client = Client::where('username', $client_username)->first();

            if ($client) {
                $client->bkash_checkout_auth_token = $id_token;
                $client->save();
                $this->create_payment($id_token, $bkashSetting);
            } else {
                return back()->with('error', 'Client not found!');
            }
        } elseif ($client_username == 'reseller') {
            // Find the reseller by resellerId
            $curentResellerId = User::find($request->resellerId);
            // dd($curentResellerId);
            $reseller = Reseller::where('resellerId', $curentResellerId->resellerId)->first();
            // dd($reseller);

            if ($reseller) {
                $reseller->bkash_checkout_auth_token = $id_token;
                $reseller->save();
                $this->create_payment($id_token, $bkashSetting, $recharge_amount, $curentResellerId);
            } else {
                return back()->with('error', 'Client not found!');
            }
        } else {
            return back()->with('error', 'Username is missing!');
        }
    }

    public function create_payment($token, $bkashSetting, $recharge_amount = null, $reseller = null)
    {
        if (!$token) {
            return back()->with('error', 'Unauthenticate User!');
        }

        // For only user if not found reseller request start here
    // dd($reseller && $recharge_amount);
        if($reseller && $recharge_amount){
            $requestHeaders = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => $token,
                'X-App-Key' => $bkashSetting->app_key
            ];
            // dd(config('app.url') . '/isp/pay');
            
            $requestBody = [
                "mode" => "0011", // default as bkash api config
                "payerReference" => $reseller->phone ?? "", // client phone number
                "callbackURL" => rtrim(config('app.url'), '/') . '/adminisp/recharge',
                "merchantAssociationInfo" => "MI05MID54RF09123456One", // default as bkash api config
                "amount" => $recharge_amount, // recharge amount
                "currency" => "BDT", // default as bkash api config
                "intent" => "sale", // default as bkash api config
                "merchantInvoiceNumber" => "Inv-" . rand(999,99999) . $recharge_amount, // client invoice due invoice id
            ];
    
            $createPaymentAPI = $bkashSetting->create_payment_api_endpoint;
         
            $createPaymentAPIRequest = null;
    
            if ($bkashSetting && $requestHeaders && $requestBody) {
                    //  dd($bkashSetting, $requestHeaders, $requestBody);
                $createPaymentAPIRequest = Http::withHeaders($requestHeaders)->post($createPaymentAPI, $requestBody)->json();
             
                
            } else {
                return back()->with('error', 'Request Failled, Try again letter!');
            }
    
            if (!empty($createPaymentAPIRequest['bkashURL'])) {
                return redirect($createPaymentAPIRequest['bkashURL'])->send();
            } else {
                return back()->with('error', 'Bkash Server not responding, try again!');
            }
        }else{
            $client = Auth::user();
            $all_due_invoices = Invoice::where('client_id', $client->id)
                ->where('due', '>', 0)
                ->whereNull('deleted_at') // Correct way to check for NULL
                ->get();
    
            $total_due = $all_due_invoices->sum('due');
            $last_invoice_id = $all_due_invoices->last()->id;
    
            if ($total_due == 0) {
                return back()->with('success', 'Already paid! Cotact with support if you have any issue.');
            } elseif ($total_due != $client->due) {
                $client->due = $total_due;
                $client->save();
            }
    
            $requestHeaders = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => $token,
                'X-App-Key' => $bkashSetting->app_key
            ];
            // dd(config('app.url') . '/isp/pay');
            $requestBody = [
                "mode" => "0011", // default as bkash api config
                "payerReference" => $client->phone ?? "", // client phone number
                "callbackURL" => rtrim(config('app.url'), '/') . '/pay',
                "merchantAssociationInfo" => "MI05MID54RF09123456One", // default as bkash api config
                "amount" => $total_due, // client due
                "currency" => "BDT", // default as bkash api config
                "intent" => "sale", // default as bkash api config
                "merchantInvoiceNumber" => "Inv-" . $last_invoice_id, // client invoice due invoice id
            ];
    
            $createPaymentAPI = $bkashSetting->create_payment_api_endpoint;
            $createPaymentAPIRequest = null;
        
            if ($bkashSetting && $requestHeaders && $requestBody) {
                // dd($bkashSetting, $requestHeaders, $requestBody, $createPaymentAPI);
                $createPaymentAPIRequest = Http::withHeaders($requestHeaders)->post($createPaymentAPI, $requestBody)->json();
                // dd($createPaymentAPIRequest);
            } else {
                return back()->with('error', 'Request Failled, Try again letter!');
            }
    
            if (!empty($createPaymentAPIRequest['bkashURL'])) {
                return redirect($createPaymentAPIRequest['bkashURL'])->send();
            } else {
                return back()->with('error', 'Bkash Server not responding, try again!');
            }
        }
    }
}
