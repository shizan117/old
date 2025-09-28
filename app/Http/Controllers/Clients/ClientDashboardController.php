<?php

/**
 * Created by PhpStorm.
 * User: DEELKO
 * Date: 7/3/2019
 * Time: 8:15 PM
 */

namespace App\Http\Controllers\Clients;

use App\Account;
use App\BkashCheckoutSetting;
use App\BkashWebhook;
use App\Client;
use App\ClientPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Invoice;
use App\Mikrotik;
use App\Plan;
use App\ResellerPlan;
use App\SMS\DeelkoSMS;
use App\Transaction;
use DB;
use App\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Log;
use MikrotikAPI\Roar\Roar,
    MikrotikAPI\Commands\PPP\Secret,
    MikrotikAPI\Commands\IP\Hotspot\HotspotUsers;

class ClientDashboardController extends Controller
{
    public function index()
    {

        $user = Auth::guard('web')->user();
        $invoices = Invoice::where('client_id', $user->id)->orderBy('id', 'DESC')->limit(6)->get();

        return view('pages.dashboard', compact('user', 'invoices'));
    }

    public function invoice()
    {
        $invoices = Invoice::where('client_id', Auth::guard('web')->user()->id)->get();
        $page_title = 'All Invoice List';
        return view('pages.invoice', compact('invoices', 'page_title'));
    }

    public function receipt()
    {
        $payments = ClientPayment::where('client_id', Auth::guard('web')->user()->id)->get();
        $page_title = 'All Receipt List';
        return view('pages.receipt_list', compact('payments', 'page_title'));
    }

    public function receiptShow($id)
    {
        $payment = ClientPayment::with('client')->where("client_id", Auth::guard('web')->user()->id)->find($id);

        if ($payment != '') {
            return view('pages.receipt_view', compact('payment'));
        } else {
            Session::flash('message', 'Receipt Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('client.invoice.view');
        }
    }


    public function invoiceShow($id)
    {
        $invoice = Invoice::with('client')->find($id);


        if ($invoice != '') {
            $client = Client::with([
                'plan' => function ($query) {
                    $query->get();
                }
            ])->find($invoice->client->id);

            $pdf = PDF::loadView('pages.invoice_show', ['client' => $client, 'invoice' => $invoice]);
            return $pdf->download('invoice.pdf');

            return view('pages.invoice_show', compact('client', 'invoice'));
        } else {
            return 'Invoice Not Found!';
        }
    }

    public function pay(){
        $client = Client::find(Auth::guard('web')->user()->id);
          $resellerPlan = DB::table('resellers')
        ->join('clients', 'resellers.resellerId', 'clients.resellerId')
        ->join('reseller_plans', 'resellers.resellerId', '=', 'reseller_plans.resellerId')
        ->where('resellers.resellerId',$client->resellerId)
        ->get(); // Assuming you want only one reseller plan for the client

        $page_title = 'Pay Due';
        if ($client != '') {
            if($client->resellerId == null){
                $charge_data = \App\Config::where('config_title', 'bkash_charge')->first();
                $charge = $charge_data->value;
            } else {
                $r = \App\Reseller::find($client->resellerId);
                $charge = $r->bkash_charge;
            }
            if($charge == 'YES'){
                $ch = ceil((2/100)*$client->due);
            } else {
                $ch = 0.00;
            }
            $due = $client->due;
            Session::put('payer_id', Auth::user()->id);
            Session::put('payer_type', 'client');
            Session::put('invoice_amount', $due+$ch);
            Session::put('charge', $ch);
            $create_payment_url = route('bkash-create-payment');
            $ex_payment_url = route('bkash-execute-payment');
            $success_url = route('client.pay');
            return view('pages.pay', compact( 'client', 'page_title', 'due', 'charge','resellerPlan', 'ch', 'create_payment_url', 'ex_payment_url', 'success_url'));
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('home');
        }
    }

    public function profileEdit()
    {
        $profile = Auth::user();
        return view('pages.profile_edit', compact('profile'));
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'email' => 'required|unique:clients,email,' . $user->id . '',
            'new_password' => 'required_with:old_password|confirmed',
            'old_password' => 'required_with:new_password',
            'phone' => 'numeric|digits_between:1,12',
            'image' => 'image|mimes:jpeg,jpg,png|max:1024|file',
            //            'new_server_password' => 'confirmed',
        ]);

        if (!empty($request->old_password)) {
            if (!\Hash::check($request->old_password, $user->password)) {
                return redirect()->back()->withErrors([
                    'old_password' => 'The current password is incorrect.'
                ]);
            } else {
                $inputs = [
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => bcrypt($request->new_password),
                    //                    'server_password' => $request->new_server_password
                ];
            }
        } else {
            $inputs = [
                'email' => $request->email,
                'phone' => $request->phone,
                //                'server_password' => $request->new_server_password
            ];
        }

        $fileName = null;
        if (request()->hasFile('image')) {
            $file = request()->file('image');
            $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
            $path = 'assets/images/clients';
            File::delete('assets/images/clients/' . $user->user_image);
            $file->move($path, $fileName);
        }
        $inputs['user_image'] = $fileName;

        if ($user->update($inputs)) {
            Session::flash('message', 'Profile Update Successful');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('client.profile.edit');
        } else {
            Session::flash('message', 'Profile Update Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }

        // CHANGE PASSWORD TO MIKROTIK
        //        if(!empty($request->new_server_password)){
        //            if($user->status == 'On'){
        //                $con = Roar::connect($user->plan->server->server_ip,
        //                    $user->plan->server->server_port,
        //                    $user->plan->server->username,
        //                    $user->plan->server->password);
        //                $serverData['password'] = $request->new_server_password;
        //                if($user->plan->type == 'PPPOE'){
        //                    $updateToServer = new Secret($con);
        //                    $serverUserId = $updateToServer->getId($user->username);
        //                } else {
        //                    $updateToServer = new HotspotUsers($con);
        //                    $serverUserId = $updateToServer->getId($user->username);
        //                }
        //
        //                if(!empty($serverUserId)){
        //                    $updateToServer->set($serverData, $serverUserId);
        //                    if ($user->update($inputs)) {
        //                        Session::flash('message', 'Profile Update Successful');
        //                        Session::flash('m-class', 'alert-success');
        //                        return redirect()->route('client.profile.edit');
        //                    } else {
        //                        Session::flash('message', 'Profile Update Failed!');
        //                        Session::flash('m-class', 'alert-danger');
        //                        return redirect()->back();
        //                    }
        //                }
        //            }
        //        }else{
        //            if ($user->update($inputs)) {
        //                Session::flash('message', 'Profile Update Successful');
        //                Session::flash('m-class', 'alert-success');
        //                return redirect()->route('client.profile.edit');
        //            } else {
        //                Session::flash('message', 'Profile Update Failed!');
        //                Session::flash('m-class', 'alert-danger');
        //                return redirect()->back();
        //            }
        //
        //        }
    }
}
