<?php

namespace App\Http\Controllers\Admin;

use App\BkashCheckoutSetting;
use App\Client;
use App\Jobs\SMSJob;
use App\Reseller;
use App\SMS\DeelkoSMS;
use Illuminate\Http\Request;
use App\Config;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use MikrotikAPI\Roar\Roar,
    MikrotikAPI\Commands\PPP\Secret,
    MikrotikAPI\Commands\PPP\Active,
    MikrotikAPI\Commands\IP\Hotspot\HotspotUsers,
    MikrotikAPI\Commands\IP\Hotspot\HotspotActive,
    MikrotikAPI\Commands\IP\Firewall\FirewallAddressList,
    MikrotikAPI\Commands\Queues\Simple;

class ConfigController extends Controller
{
    public function index()
    {
        if (Auth::user()->resellerId == '') {
            return view('admin.pages.setting');
        } else {
            $r = Reseller::find(Auth::user()->resellerId);
            return view('admin.pages.setting', compact('r'));
        }
    }

    public function store(Request $request)
    {

        if (Auth::user()->resellerId == '') {
            $this->validate($request, [
                'companyName' => 'required',
                'logo' => 'image|mimes:jpeg,jpg,png|max:1024|file',
                'company_signature' => 'image|mimes:jpeg,jpg,png|max:1024|file',
                'address' => 'required',
                'phone' => 'required|numeric',
                //                'currencyCode' => 'required',
                'vatRate' => 'required|numeric',
                'invoice_system' => 'required',
                'print_receipt_after_payment' => 'required',
                'exp_date' => 'required_if:invoice_system,fixed|numeric|min:1|max:28',
                'exp_time' => 'required|date_format:H:i',
                'expire_client_days' => 'required|numeric|min:0|max:28'
            ], [
                'image.dimensions' => 'Please Upload 200x200 pixel size image!'
            ]);

            //            echo $request->exp_time;die;

            DB::beginTransaction();
            try {
                foreach ($request->all() as $key => $value) {
                    if ($key != '_token') {
                        $config = Config::where('config_title', $key)->first();
                        if (!empty($config)) {
                            $config->update(['value' => $value]);
                        }
                    }
                }

                if (request()->hasFile('logo')) {
                    $logo = Config::where('config_title', 'logo')->first();
                    $file = request()->file('logo');
                    $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                    $path = ('assets/images/');
                    File::delete('assets/images/' . $logo->value);
                    $file->move($path, $fileName);
                    $logo->update(['value' => $fileName]);
                }

                if (request()->hasFile('company_signature')) {
                    $signature = Config::where('config_title', 'company_signature')->first();
                    $file = request()->file('company_signature');
                    $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                    $path = ('assets/images/');
                    File::delete('assets/images/' . $signature->value);
                    $file->move($path, $fileName);
                    $signature->update(['value' => $fileName]);
                }


                DB::commit();
                Session::flash('message', 'Config Update Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('config.index');
            } catch (\Exception $e) {
                DB::rollBack();
                Session::flash('message', 'Config Update failed ' . $e);
                Session::flash('m-class', 'alert-danger');
                return redirect()->route('config.index');
            }
        } else {

            $this->validate($request, [
                'business_name' => 'required',
                'phone' => 'required',
                'resellerLocation' => 'required',
                'logo' => 'image|mimes:jpeg,jpg,png|max:1024|file',
                'signature' => 'image|mimes:jpeg,jpg,png|max:1024|file',
            ], [
                'image.dimensions' => 'Please Upload 200x200 pixel size image!'
            ]);

            $input['business_name'] = $request->business_name;
            $input['phone'] = $request->phone;
            $input['resellerLocation'] = $request->resellerLocation;
            $input['notice'] = $request->notice;
            $input['prefix'] = $request->prefix;

            $reseller = Reseller::find(Auth::user()->resellerId);

            if (request()->hasFile('logo')) {
                $logo = $reseller->logo;
                $file = request()->file('logo');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $path = ('assets/images/');
                if ($logo != '') {
                    File::delete('assets/images/' . $logo);
                }
                $file->move($path, $fileName);
                $input['logo'] = $fileName;
            }
            if (request()->hasFile('signature')) {
                $signature = $reseller->signature;
                $file = request()->file('signature');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $path = ('assets/images/');
                if ($signature != '') {
                    File::delete('assets/images/' . $signature);
                }
                $file->move($path, $fileName);
                $input['signature'] = $fileName;
            }


            if ($reseller->update($input)) {
                Session::flash('message', 'Setting Update Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->back();
            } else {
                Session::flash('message', 'Setting Update Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
    }


    public function resellerClientInactive($resellerID)
    {
        $resellerClients = Client::where('resellerID', $resellerID)->where('status', 'On')
            ->where('server_status', 1)->pluck('id');

        $inputs['status'] = 'Off';
        foreach ($resellerClients as $c_id) {
            $client_Data = Client::find($c_id);

            if (!empty($client_Data) && $client_Data->server_status == '1' && $client_Data->status == 'On') {

                if (setting('using_mikrotik')) {
                    $con = Roar::connect($client_Data->plan->server->server_ip, $client_Data->plan->server->server_port, $client_Data->plan->server->username, encrypt_decrypt('decrypt', $client_Data->plan->server->password));
                    if ($con->isConnected()) {

                        if ($client_Data->plan->type == 'PPPOE') {
                            $clientData['profile'] = 'Redirect Profile';
                            $updateToServer = new Secret($con);
                            $serverUserId = $updateToServer->getId($client_Data->username);
                        } elseif ($client_Data->plan->type == 'Hotspot') {
                            $clientData['profile'] = 'Redirect Profile';
                            $updateToServer = new HotspotUsers($con);
                            $serverUserId = $updateToServer->getId($client_Data->username);
                        } else {
                            $updateToServer = new Simple($con);
                            $serverUserId = $updateToServer->getId($client_Data->client_name . " (" . $client_Data->username . ")");
                        }

                        if (!empty($serverUserId)) {
                            if ($client_Data->plan->type == 'PPPOE') {
                                $updateToServer->set($clientData, $serverUserId);
                                $delActive = new Active($con);
                                $oldActiveId = $delActive->getId($client_Data->username);
                                $delActive->delete($oldActiveId);
                            } elseif ($client_Data->plan->type == 'Hotspot') {
                                $updateToServer->set($clientData, $serverUserId);
                                $delActive = new HotspotActive($con);
                                $oldActiveId = $delActive->getId($client_Data->username);
                                $delActive->delete($oldActiveId);
                            } else {
                                $firewall_address = new FirewallAddressList($con);
                                $fire_ip_data = [
                                    'address' => $client_Data->client_ip,
                                    'list' => 'Redirect IP'
                                ];
                                $firewall_address->add($fire_ip_data);
                            }

                            $client_Data->update($inputs);
                        }
                    } else {
                        return false;
                    }
                } else {
                    $client_Data->update($inputs);
                }
            }
        }

        return true;
    }

    public function paymentApi()
    {
        if (Auth::user()->resellerId == '') {
            $paymentSetting = BkashCheckoutSetting::whereNull('resellerId')->latest()->first();
            return view('admin.pages.setting_payment_api', compact('paymentSetting'));
        } 
        else {
            $paymentSetting = BkashCheckoutSetting::find(Auth::user()->resellerId);
            return view('admin.pages.setting_payment_api', compact('paymentSetting'));
        }
    }

    public function bkashApiStore(Request $request)
    {
        $this->validate($request, [
            //            'bkash_username' => 'required',
            //            'bkash_password' => 'required',
            //            'bkash_app_key' => 'required',
            //            'bkash_app_secret' => 'required',
            //            'bkash_charge' => 'required',
            //            'bkash_url' => 'required',
            //            'bkash_production_root_url' => 'required'
        ]);

        if (Auth::user()->resellerId == '') {
            $input_user['value'] = $request->bkash_username;
            $username = Config::where('config_title', 'bkash_username')->first();
            if ($username->update($input_user)) {
                $inputPass['value'] = $request->bkash_password;
                $b_pass = Config::where('config_title', 'bkash_password')->first();
                if ($b_pass->update($inputPass)) {
                    $app_key['value'] = $request->bkash_app_key;
                    $appKey = Config::where('config_title', 'bkash_app_key')->first();
                    if ($appKey->update($app_key)) {

                        $app_secret['value'] = $request->bkash_app_secret;
                        $appSecret = Config::where('config_title', 'bkash_app_secret')->first();
                        if ($appSecret->update($app_secret)) {

                            $bkash_charge['value'] = $request->bkash_charge;
                            $charge = Config::where('config_title', 'bkash_charge')->first();
                            if ($charge->update($bkash_charge)) {
                                $bkash_url['value'] = $request->bkash_url;
                                $url = Config::where('config_title', 'bkash_checkout_script_url')->first();
                                if ($url->update($bkash_url)) {
                                    $bkash_pr_url['value'] = $request->bkash_production_root_url;
                                    $p_url = Config::where('config_title', 'bkash_pr_root_url')->first();
                                    if ($p_url->update($bkash_pr_url)) {
                                        Session::flash('message', 'bKash Data Update Successful ');
                                        Session::flash('m-class', 'alert-success');
                                        return redirect()->back();
                                    } else {
                                        Session::flash('message', 'bKash Production Url Update Failed!');
                                        Session::flash('m-class', 'alert-danger');
                                        return redirect()->back();
                                    }
                                } else {
                                    Session::flash('message', 'bKash Script Url Update Failed!');
                                    Session::flash('m-class', 'alert-danger');
                                    return redirect()->back();
                                }
                            } else {
                                Session::flash('message', 'bKash Charge Update Failed!');
                                Session::flash('m-class', 'alert-danger');
                                return redirect()->back();
                            }
                        } else {
                            Session::flash('message', 'App Secret Update Failed!');
                            Session::flash('m-class', 'alert-danger');
                            return redirect()->back();
                        }
                    } else {
                        Session::flash('message', 'App Key Update Failed!');
                        Session::flash('m-class', 'alert-danger');
                        return redirect()->back();
                    }
                } else {
                    Session::flash('message', 'Password Update Failed!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
            } else {
                Session::flash('message', 'Username Update Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        } else {

            $input = [
                'bkash_username' => $request->bkash_username,
                'bkash_password' => $request->bkash_password,
                'bkash_app_key' => $request->bkash_app_key,
                'bkash_app_secret' => $request->bkash_app_secret,
                'bkash_charge' => $request->bkash_charge,
                'bkash_url' => $request->bkash_url,
                'bkash_production_root_url' => $request->bkash_production_root_url
            ];

            $reseller = Reseller::find(Auth::user()->resellerId);
            if ($reseller->update($input)) {
                Session::flash('message', 'bKash Data Update Successful ');
                Session::flash('m-class', 'alert-success');
                return redirect()->back();
            } else {
                Session::flash('message', 'bKash Data Update Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
    }

    public function nagadApiStore(Request $request)
    {
        //        $this->validate($request, [
        //            'nagad_merchant_id' => 'required',
        //            'nagad_merchant_number' => 'required',
        //            'nagad_pg_public_key' => 'required',
        //            'nagad_merchant_private_key' => 'required',
        //        ]);

        if (Auth::user()->resellerId == '') {

            Config::where('config_title', 'nagad_merchant_id')->update(['value' => $request->nagad_merchant_id]);
            Config::where('config_title', 'nagad_merchant_number')->update(['value' => $request->nagad_merchant_number]);
            Config::where('config_title', 'nagad_pg_public_key')->update(['value' => $request->nagad_pg_public_key]);
            Config::where('config_title', 'nagad_merchant_private_key')->update(['value' => $request->nagad_merchant_private_key]);
            Config::where('config_title', 'nagad_charge')->update(['value' => $request->nagad_charge]);

            Session::flash('message', 'Nagad Data Update Successful ');
            Session::flash('m-class', 'alert-success');
            return redirect()->back();
        } else {

            $input = [
                'nagad_merchant_id' => $request->nagad_merchant_id,
                'nagad_merchant_number' => $request->nagad_merchant_number,
                'nagad_pg_public_key' => $request->nagad_pg_public_key,
                'nagad_merchant_private_key' => $request->nagad_merchant_private_key,
                'nagad_charge' => $request->nagad_charge,
            ];

            $reseller = Reseller::find(Auth::user()->resellerId);
            if ($reseller->update($input)) {
                Session::flash('message', 'Nagad Data Update Successful ');
                Session::flash('m-class', 'alert-success');
                return redirect()->back();
            } else {
                Session::flash('message', 'Nagad Data Update Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
    }

    public function profileEdit()
    {
        $profile = Auth::user();
        return view('admin.pages.profile_edit', compact('profile'));
    }

    public function profileUpdate(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $request->validate([
            'email' => 'required|unique:clients,email,' . $user->id . '',
            'new_password' => 'required_with:old_password|confirmed',
            'old_password' => 'required_with:new_password',
            'phone' => 'numeric|digits_between:1,12',
            'image' => 'image|mimes:jpeg,jpg,png|max:1024|file',
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
                    'password' => bcrypt($request->new_password)
                ];
            }
        } else {
            $inputs = [
                'email' => $request->email,
                'phone' => $request->phone
            ];
        }
        $fileName = null;
        if (request()->hasFile('image')) {
            $file = request()->file('image');
            $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
            $path = 'assets/images/users';
            File::delete('assets/images/users/' . $user->user_image);
            $file->move($path, $fileName);
        }
        $inputs['user_image'] = $fileName;

        if ($user->update($inputs)) {
            Session::flash('message', 'Profile Update Successful');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('profile.edit');
        } else {
            Session::flash('message', 'Profile Update Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }


    public function smsSetup(Request $request)
    {
        $clientData = Client::with(['plan'])->where('resellerId', Auth::user()->resellerId)
            ->where('branchId', Auth::user()->branchId);
        if ($request->clientType == 'active') {
            $clientData = $clientData->where('status', 'On')->where('server_status', 1);
        } elseif ($request->clientType == 'inactive') {
            $clientData = $clientData->where('status', 'Off')->where('server_status', 1);
        } elseif ($request->clientType == 'old') {
            $clientData = $clientData->where('status', 'Off')->where('server_status', 2);
        } elseif ($request->clientType == 'due') {
            $clientData = $clientData->where('due', '>', 0)->where('server_status', 1);
        }

        $clientData = $clientData->orderBy('username', 'ASC')->get();

        $sms_client_id = '';

        if (Auth::user()->resellerId != null) {
            $sms_api_key = Reseller::where('resellerId', Auth::user()->resellerId)->value('sms_api_key');
            $sms_client_id = Reseller::where('resellerId', Auth::user()->resellerId)->value('sms_client_id');;
        } else {
            $sms_api_key = setting('sms_api_key');
            $sms_client_id = setting('sms_client_id');
        }
        // $sms_client_id = "DEELKO.COM";
        // $sms_balance = '';
        // if($sms_api_key != ''){
        //     $sms_balance = file_get_contents(' http://smpp.revesms.com/sms/smsConfiguration/smsClientBalance.jsp?'.$sms_client_id);
        // }


        $sms_balance = '';

        if ($sms_api_key != '') {
            // Get the API response using file_get_contents
            $response = file_get_contents('http://smpp.revesms.com/sms/smsConfiguration/smsClientBalance.jsp?client=' . $sms_client_id);

            // Decode the JSON response
            $decoded_response = json_decode($response, true);

            // Check if Balance is present in the response and get its value
            if (isset($decoded_response['Balance'])) {
                $sms_balance = $decoded_response['Balance'] . " TK";
            } else {
                // Handle if Balance is not present or response is invalid
                $sms_balance = 'Balance not found or invalid response';
            }
        }

        // Now $sms_balance will contain the balance value (e.g., 97.34) or an error message

        if (Auth::user()->resellerId == '') {
            return view('admin.pages.sms_content', compact('clientData', 'sms_balance'));
        } else {
            $r = Reseller::find(Auth::user()->resellerId);
            return view('admin.pages.sms_content', compact('clientData', 'r', 'sms_balance'));
        }
    }

    public function smsSetupUpdate(Request $request)
    {
        if (Auth::user()->resellerId == '') {
            Config::where('config_title', 'sms_remainder')->update(['value' => $request->smsRemainder]);
            Config::where('config_title', 'sms_payment')->update(['value' => $request->smsPayment]);
            Config::where('config_title', 'sms_disconnect')->update(['value' => $request->smsDisconnect]);
            Config::where('config_title', 'sms_invoice')->update(['value' => $request->smsInvoice]);
            Config::where('config_title', 'sms_new_client')->update(['value' => $request->smsNewClient]);
            Config::where('config_title', 'sms_payment_to_reseller')->update(['value' => $request->smsReseller]);
            Config::where('config_title', 'sms_custom')->update(['value' => $request->smsCustom]);

            Session::flash('message', 'SMS Content Updated!');
            Session::flash('m-class', 'alert-success');
            return redirect()->back();
        } else {
            $input = [
                'sms_invoice' => $request->smsInvoice,
                'sms_payment' => $request->smsPayment,
                'sms_remainder' => $request->smsRemainder,
                'sms_disconnect' => $request->smsDisconnect,
                'sms_new_client' => $request->smsNewClient,
                'sms_payment_to_reseller' => $request->smsReseller,
                'sms_custom' => $request->smsCustom,
            ];
            $sms = Reseller::find(Auth::user()->resellerId);
            if ($sms->update($input)) {
                Session::flash('message', 'SMS Content Successful ');
                Session::flash('m-class', 'alert-success');
                return redirect()->back();
            } else {
                Session::flash('message', 'SMS Content Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
    }

    public function smsAPIUpdate(Request $request)
    {
        $request->validate([
            'sms_api_key' => 'required|string',
            'sms_masking_id' => 'required|string',
            'sms_is_active' => 'required|string',
            'sms_secret_key' => 'required|string',
            'sms_client_id' => 'required|string',
        ]);
        if (Auth::user()->resellerId == '') {
            // Config::where('config_title', 'sms_api_url')->update(['value' => $request->sms_api_url]);
            Config::where('config_title', 'sms_api_key')->update(['value' => $request->sms_api_key]);
            Config::where('config_title', 'sms_masking_id')->update(['value' => $request->sms_masking_id]);
            Config::where('config_title', 'sms_is_active')->update(['value' => $request->sms_is_active]);
            Config::where('config_title', 'sms_secret_key')->update(['value' => $request->sms_secret_key]);
            Config::where('config_title', 'sms_client_id')->update(['value' => $request->sms_client_id]);


            Session::flash('message', 'Content Updated!');
            Session::flash('m-class', 'alert-success');
            return redirect()->back();
        } else {
            $input = [
                // 'sms_api_url' => $request->sms_api_url,
                'sms_api_key' => $request->sms_api_key,
                'sms_masking_id' => $request->sms_masking_id,
                'sms_secret_key' => $request->sms_secret_key,
                'sms_client_id' => $request->sms_client_id,
                'sms_is_active' => $request->sms_is_active,
            ];

            //            print_r($input);die;
            $sms = Reseller::find(Auth::user()->resellerId);
            if ($sms->update($input)) {
                Session::flash('message', 'SMS Api Update Successful');
                Session::flash('m-class', 'alert-success');
                return redirect()->back();
            } else {
                Session::flash('message', 'SMS Api Update Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
    }

    public function customSMSSend(Request $request)
    {
        $request->validate([
            'smsType' => 'required'
        ]);

        if (Auth::user()->resellerId == '') {
            $isActive = Config::where('config_title', 'sms_is_active')->get();
            if (!$isActive) {
                Session::flash('message', 'SMS API is Deactiveted!');
                Session::flash('m-class', 'alert-warning');
                return redirect()->back();
            }
        } else {
            $isActive = Reseller::where('resellerId', Auth::user()->resellerId)->value('sms_is_active');
            if (!$isActive) {
                Session::flash('message', 'SMS API is Deactiveted!');
                Session::flash('m-class', 'alert-warning');
                return redirect()->back();
            }
        }

        //        $_success = [];



        foreach ($request->clientID as $id) {
            $client = Client::find($id);

            //========NEW CLIENT SMS=======

            $this->dispatch(new SMSJob($client, $request->smsType));
            //            $deelkoSMS = new DeelkoSMS();
            //            $deelkoSMS->sendSMS($client, $request->smsType);
            //            if($deelkoSMS->sendSMS($client, $request->smsType)){
            //                $_success[] = $client->client_name;
            //            };
        }
        //        Session::flash('message', 'SMS Successfully Send to:- '. implode(", ",$_success));
        Session::flash('message', 'SMS Successfully Sent!');
        Session::flash('m-class', 'alert-success');
        return redirect()->back();
    }
}
