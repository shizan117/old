<?php

namespace App\Http\Controllers;

use App\Account;
use App\Bandwidth;
use App\ClientPayment;
use App\Config;
use App\ResellerPayment;
use App\SMS\DeelkoSMS;
use App\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use MikrotikAPI\Roar\Roar;
use MikrotikAPI\Commands\IP\Hotspot\HotspotUsers;
use MikrotikAPI\Commands\IP\Hotspot\HotspotActive;
use MikrotikAPI\Commands\IP\Firewall\FirewallAddressList;
use MikrotikAPI\Commands\PPP\Secret;
use MikrotikAPI\Commands\PPP\Active;
use MikrotikAPI\Commands\Queues\Simple;
use App\Reseller;
use App\Client;
use App\Invoice;

class BkashPayment extends Controller
{
    public function clientPayment($id, $amount,$payment_info)
    {
        $client = Client::with([
            'plan' => function ($query) {
                $query->get();
            },
            'plan.server' => function ($query) {
                $query->get();
            }
        ])->find($id);


        $paid = $client->due;
        $bkashCharge = $amount - $client->due;
        $ad_payment = 0.00;
        $due = 0.00;
        $update_client = [
            'due' => $due
        ];


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
            'new_paid' => $paid,
            'paid_from_advance' => 0.00,
            'pre_balance' => $client->balance,
            'due' => $due,
            'payment_date' => date('Y-m-d'),
            'branchId' => $client->branchId,
            'resellerId' => $client->resellerId
        ];

        $ac = Account::whereNull('resellerId')->where('account_type', 'bKash')->first();
        $account_balance = $ac->account_balance + $paid;
        $ac_inputs = [
            'account_balance' => $account_balance
        ];

        $invoice_system = Config::where('config_title', 'invoice_system')->first()->value;
        if ($client->resellerId != null) {
            $reseller = Reseller::find($client->resellerId);
            $vatRate = $reseller->vat_rate;
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

            $update_client ['status'] = 'On';
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
            $update_client ['status'] = 'On';
            if ($client->plan->type == 'PPPOE') {
                $clientData['profile'] = $client->plan->plan_name;
            } elseif ($client->plan->type == 'Hotspot') {
                $clientData['profile'] = $client->plan->plan_name;
            } else {
                $clientData['target'] = $client->client_ip;
            }
        }

        $exp_time = explode(':',setting('exp_time'));
        if ($client->plan->duration_unit == '2') {
            $date_exp = date("Y-m-d H:i:s", mktime($exp_time[0],$exp_time[1],'00', date("m"), date("d") + $client->plan->duration, date("Y")));
        } else {
            $date_exp = date("Y-m-d H:i:s", mktime($exp_time[0],$exp_time[1],'00', date("m") + $client->plan->duration, date($c_exp_date), date("Y")));
        }

        if ($vat > 0) {
            if ($paid > $service_charge) {
                $p_price_paid = $paid - $service_charge;
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
            'tr_amount' => $paid,
            'tr_vat' => $vat_paid,
            'charge' => $bkashCharge,
            'payer' => $client->client_name,
            'bkash_trxID' => $payment_info['trxID'],
            'cr' => $amount,
            'branchId' => $client->branchId,
            'resellerId' => $client->resellerId,
            'trans_date' => date('Y-m-d')
        ];

        if ($client->start_transaction == "") {
            $update_client ['start_transaction'] = date("Y-m-d");
        }

        if (empty($invoice)) {
            $pay_id = ClientPayment::create($payment_inputs)->id;
            if (!empty($pay_id)) {
                $tr_inputs['pay_id'] = $pay_id;

                if (Transaction::create($tr_inputs)) {

                    if ($client->update($update_client)) {
                        $ac->update($ac_inputs);

                        // if ($client->resellerId != null) {
                        //     $reseller_data=[];
                        //     if ($invoice->paid_amount == 0) {
                        //         if ($invoice->buy_price > $reseller->balance) {
                        //             Session::flash('message', 'Reseller credit is low to take payment. Contact with upstream!');
                        //             Session::flash('m-class', 'alert-danger');
                        //             return back();
                        //         }
                        //         $reseller_data = [
                        //             'balance' => $reseller->balance - $invoice->buy_price
                        //         ];
                        //     }
                        //     $reseller->update($reseller_data);
                        // }
                        //========PAYMENT CONFIRMATION SMS=======
                        $deelkoSMS = new DeelkoSMS();
                        $deelkoSMS->sendSMS($client,'sms_payment',$paid);
                        Session::flash('message', 'Payment has been Completed Successfully');
                        Session::flash('m-class', 'alert-success');
                        return back();
                    } else {
                        Session::flash('message', 'Client Data Update Error. Contact Please');
                        Session::flash('m-class', 'alert-danger');
                        return back();
                    }
                } else {
                    Session::flash('message', 'Transaction Create Failed! Contact Please');
                    Session::flash('m-class', 'alert-danger');
                    return back();
                }

            } else {
                Session::flash('message', 'Client Payment Create Failed! Contact Please');
                Session::flash('m-class', 'alert-danger');
                return back();
            }
        } else {
            $allInvoices = Invoice::where('client_id', $id)->where('due', '!=', 0)
                ->orderBy('bill_year', 'ASC')->orderBy('bill_month', 'ASC')->get();

            foreach ($allInvoices as $singleInvoice) {
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

                $update_client ['expiration'] = $date_exp;


                $inv = Invoice::find($singleInvoice['id']);

                if ($client->resellerId != null) {
                    $reseller_data=[];
                    if ($inv->paid_amount == 0) {
                        if ($inv->buy_price > $reseller->balance) {
                            Session::flash('message', 'Reseller credit is low to take payment. Contact with upstream!');
                            Session::flash('m-class', 'alert-danger');
                            return back();
                        }
                        $reseller_data = [
                            'balance' => $reseller->balance - $invoice->buy_price
                        ];
                    }
                }

                $inv->update($inputs);
                $paid = $paid - $singleInvoice['due'];

                if ($paid <= 0) {
                    if ($client->status == 'Off') {

                        if(setting('using_mikrotik')) {
                            $con = Roar::connect($client->plan->server->server_ip, $client->plan->server->server_port, $client->plan->server->username, encrypt_decrypt('decrypt', $client->plan->server->password));
                            if ($con->isConnected()) {
                                if ($client->server_status == 2) {
                                    $update_client ['server_status'] = 1;
                                    $update_client ['server_active_date'] = date('Y-m-d');
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
                                        $b = Bandwidth::find($client->plan->bandwidth_id);
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

                                        if (Auth::user()->roleId == 4 && $reseller->exp_date > date('Y-m-d')) {
                                            $firewall_address = new FirewallAddressList($con);
                                            $fire_ip_data = [
                                                'address' => $client->client_ip,
                                                'list' => 'Redirect IP'
                                            ];
                                            $firewall_address->add($fire_ip_data);
                                        }

                                    }

                                    if (!empty($new_id)) {
                                        $pay_id = ClientPayment::create($payment_inputs)->id;
                                        if (!empty($pay_id)) {
                                            $tr_inputs['pay_id'] = $pay_id;
                                            if (Transaction::create($tr_inputs)) {
                                                if ($client->update($update_client)) {
                                                    $ac->update($ac_inputs);
                                                    if ($client->resellerId != null) {
                                                        $reseller->update($reseller_data);
                                                    }
                                                    //========PAYMENT CONFIRMATION SMS=======
                                                    $deelkoSMS = new DeelkoSMS();
                                                    $deelkoSMS->sendSMS($client, 'sms_payment', $paid);
                                                    Session::flash('message', 'Payment has been Completed Successfully');
                                                    Session::flash('m-class', 'alert-success');
                                                    return back();
                                                } else {
                                                    Session::flash('message', 'Client Data Update Failed! Contact Please');
                                                    Session::flash('m-class', 'alert-danger');
                                                    return back();
                                                }
                                            } else {
                                                Session::flash('message', 'Transaction Create Failed! Contact Please');
                                                Session::flash('m-class', 'alert-danger');
                                                return back();
                                            }
                                        } else {
                                            Session::flash('message', 'Client Payment Create Failed! Contact Please');
                                            Session::flash('m-class', 'alert-danger');
                                            return back();
                                        }

                                    } else {
                                        Session::flash('message', 'Data Create Failed To ISP Server! Contact Please');
                                        Session::flash('m-class', 'alert-danger');
                                        return back();
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
                                        $pay_id = ClientPayment::create($payment_inputs)->id;
                                        if (!empty($pay_id)) {
                                            $tr_inputs['pay_id'] = $pay_id;

                                            if (!(Transaction::create($tr_inputs))) {
                                                Session::flash('message', 'Transaction Create Failed! Contact Please');
                                                Session::flash('m-class', 'alert-danger');
                                                return back();
                                            }


                                            if ($client->update($update_client)) {
                                                $ac->update($ac_inputs);
                                                if ($client->resellerId != null) {
                                                    $reseller->update($reseller_data);
                                                }
                                                $updateToServer->set($clientData, $serverUserId);
                                                if ($client->plan->type == 'IP') {
                                                    $firewall_address = new FirewallAddressList($con);

                                                    if (Auth::user()->roleId == 4) {
//                                                    if ($reseller->exp_date <= date('Y-m-d')) {
//                                                        $get_f_id = $firewall_address->getId($client->client_ip);
//                                                        $firewall_address->delete($get_f_id);
//                                                    }
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
                                                $deelkoSMS->sendSMS($client, 'sms_payment', $paid);
                                                Session::flash('message', 'Payment has been Completed Successfully');
                                                Session::flash('m-class', 'alert-success');
                                                return back();
                                            } else {
                                                Session::flash('message', 'Client Data Update Failed! Contact Please');
                                                Session::flash('m-class', 'alert-danger');
                                                return back();
                                            }

                                        } else {
                                            Session::flash('message', 'Client Payment Create Failed! Contact Please');
                                            Session::flash('m-class', 'alert-danger');
                                            return back();
                                        }

                                    } else {
                                        Session::flash('message', 'Data Update Failed To ISP Server! Contact Please');
                                        Session::flash('m-class', 'alert-danger');
                                        return back();
                                    }
                                }
                            } else {
                                Session::flash('message', 'Connect Failed To ISP Server! Contact Please');
                                Session::flash('m-class', 'alert-danger');
                                return back();
                            }
                        } else {
                            if ($client->server_status == 2) {
                                $update_client ['server_status'] = 1;
                                $update_client ['server_active_date'] = date('Y-m-d');
                            }
                            $pay_id = ClientPayment::create($payment_inputs)->id;
                            if (!empty($pay_id)) {
                                $tr_inputs['pay_id'] = $pay_id;
                                if (Transaction::create($tr_inputs)) {
                                    if ($client->update($update_client)) {
                                        $ac->update($ac_inputs);
                                        if ($client->resellerId != null) {
                                            $reseller->update($reseller_data);
                                        }
                                        //========PAYMENT CONFIRMATION SMS=======
                                        $deelkoSMS = new DeelkoSMS();
                                        $deelkoSMS->sendSMS($client, 'sms_payment', $paid);
                                        Session::flash('message', 'Payment has been Completed Successfully');
                                        Session::flash('m-class', 'alert-success');
                                        return back();
                                    } else {
                                        Session::flash('message', 'Client Data Update Failed! Contact Please');
                                        Session::flash('m-class', 'alert-danger');
                                        return back();
                                    }
                                } else {
                                    Session::flash('message', 'Transaction Create Failed! Contact Please');
                                    Session::flash('m-class', 'alert-danger');
                                    return back();
                                }
                            } else {
                                Session::flash('message', 'Client Payment Create Failed! Contact Please');
                                Session::flash('m-class', 'alert-danger');
                                return back();
                            }


                        }

                    } else {
                        $pay_id = ClientPayment::create($payment_inputs)->id;
                        if (!empty($pay_id)) {
                            $tr_inputs['pay_id'] = $pay_id;

                            if(!(Transaction::create($tr_inputs))){
                                Session::flash('message', 'Transaction Create Failed! Contact Please');
                                Session::flash('m-class', 'alert-danger');
                                return back();
                            }

                            if ($client->update($update_client)) {
                                $ac->update($ac_inputs);

                                if ($client->resellerId != null) {
                                    $reseller->update($reseller_data);
                                }
                                //========PAYMENT CONFIRMATION SMS=======
                                $deelkoSMS = new DeelkoSMS();
                                $deelkoSMS->sendSMS($client,'sms_payment',$paid);
                                Session::flash('message', 'Payment has been Completed Successfully');
                                Session::flash('m-class', 'alert-success');
                                return back();
                            } else {
                                Session::flash('message', 'Client Data Update Failed! Contact Please');
                                Session::flash('m-class', 'alert-danger');
                                return back();
                            }
                        } else {
                            Session::flash('message', 'Client Payment Create Failed! Contact Please');
                            Session::flash('m-class', 'alert-danger');
                            return back();
                        }
                    }
                }

                if (!($invoiceCount > 1)) {
                    if ($client->status == 'Off') {
                        if(setting('using_mikrotik')) {
                            $con = Roar::connect($client->plan->server->server_ip, $client->plan->server->server_port, $client->plan->server->username, encrypt_decrypt('decrypt', $client->plan->server->password));
                            if ($con->isConnected()) {
                                if ($client->server_status == 2) {
                                    $update_client ['server_status'] = 1;
                                    $update_client ['server_active_date'] = date('Y-m-d');
                                    if ($client->plan->type == 'PPPOE') {
                                        $clientData = [
                                            'name' => $client->username,
                                            'service' => 'pppoe',
                                            'password' => $client->server_password
                                        ];
                                        $addToServer = new Secret($con);
                                        $addToServer->add($clientData);
                                        $new_id = $addToServer->getId($client->username);

                                    } else if ($client->plan->type == 'Hotspot') {
                                        $clientData = [
                                            'name' => $client->username,
                                            'password' => $client->server_password
                                        ];
                                        $addToServer = new HotspotUsers($con);
                                        $addToServer->add($clientData);
                                        $new_id = $addToServer->getId($client->username);
                                    } else {
                                        $b = Bandwidth::find($client->plan->bandwidth_id);
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

                                        if (Auth::user()->roleId == 4 && $reseller->exp_date > date('Y-m-d')) {
                                            $fire_ip_data = [
                                                'address' => $client->client_ip,
                                                'list' => 'Redirect IP'
                                            ];
                                            $firewall_address->add($fire_ip_data);
                                        }

                                    }

                                    if (!empty($new_id)) {
                                        $pay_id = ClientPayment::create($payment_inputs)->id;
                                        if (!empty($pay_id)) {
                                            $tr_inputs['pay_id'] = $pay_id;

                                            if (!(Transaction::create($tr_inputs))) {
                                                Session::flash('message', 'Transaction Create Failed! Contact Please');
                                                Session::flash('m-class', 'alert-danger');
                                                return back();
                                            }

                                            if ($client->update($update_client)) {
                                                $ac->update($ac_inputs);
                                                if ($client->resellerId != null) {
                                                    $reseller->update($reseller_data);
                                                }
                                                //========PAYMENT CONFIRMATION SMS=======
                                                $deelkoSMS = new DeelkoSMS();
                                                $deelkoSMS->sendSMS($client, 'sms_payment', $paid);
                                                Session::flash('message', 'Payment has been Completed Successfully');
                                                Session::flash('m-class', 'alert-success');
                                                return back();
                                            } else {
                                                Session::flash('message', 'Client Data Update Failed! Contact Please');
                                                Session::flash('m-class', 'alert-danger');
                                                return back();
                                            }
                                        } else {
                                            Session::flash('message', 'Client Payment Create Failed! Contact Please');
                                            Session::flash('m-class', 'alert-danger');
                                            return back();
                                        }

                                    } else {
                                        Session::flash('message', 'Data Create Failed To ISP Server! Contact Please');
                                        Session::flash('m-class', 'alert-danger');
                                        return back();
                                    }

                                }
                                else {
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
                                        $pay_id = ClientPayment::create($payment_inputs)->id;
                                        if (!empty($pay_id)) {
                                            $tr_inputs['pay_id'] = $pay_id;

                                            if (!(Transaction::create($tr_inputs))) {
                                                Session::flash('message', 'Transaction Create Failed! Contact Please');
                                                Session::flash('m-class', 'alert-danger');
                                                return back();
                                            }

                                            if ($client->update($update_client)) {
                                                $ac->update($ac_inputs);
                                                if ($client->resellerId != null) {
                                                    $reseller->update($reseller_data);
                                                }
                                                $updateToServer->set($clientData, $serverUserId);
                                                if ($client->plan->type == 'IP') {
                                                    $firewall_address = new FirewallAddressList($con);
                                                    if (Auth::user()->roleId == 4) {
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
                                                $deelkoSMS->sendSMS($client, 'sms_payment', $paid);
                                                Session::flash('message', 'Payment has been Completed Successfully');
                                                Session::flash('m-class', 'alert-success');
                                                return back();
                                            } else {
                                                Session::flash('message', 'Client Data Update Failed! Contact Please');
                                                Session::flash('m-class', 'alert-danger');
                                                return back();
                                            }
                                        } else {
                                            Session::flash('message', 'Client Payment Create Failed! Contact Please');
                                            Session::flash('m-class', 'alert-danger');
                                            return back();
                                        }

                                    } else {
                                        Session::flash('message', 'Data Update Failed To ISP Server! Contact Please');
                                        Session::flash('m-class', 'alert-danger');
                                        return back();
                                    }
                                }
                            } else {
                                Session::flash('message', 'Connect Failed To ISP Server! Contact Please');
                                Session::flash('m-class', 'alert-danger');
                                return back();
                            }
                        } else {

                            if ($client->server_status == 2) {
                                $update_client ['server_status'] = 1;
                                $update_client ['server_active_date'] = date('Y-m-d');
                            }

                            $pay_id = ClientPayment::create($payment_inputs)->id;
                            if (!empty($pay_id)) {
                                $tr_inputs['pay_id'] = $pay_id;

                                if (!(Transaction::create($tr_inputs))) {
                                    Session::flash('message', 'Transaction Create Failed! Contact Please');
                                    Session::flash('m-class', 'alert-danger');
                                    return back();
                                }

                                if ($client->update($update_client)) {
                                    $ac->update($ac_inputs);
                                    if ($client->resellerId != null) {
                                        $reseller->update($reseller_data);
                                    }
                                    //========PAYMENT CONFIRMATION SMS=======
                                    $deelkoSMS = new DeelkoSMS();
                                    $deelkoSMS->sendSMS($client, 'sms_payment', $paid);
                                    Session::flash('message', 'Payment has been Completed Successfully');
                                    Session::flash('m-class', 'alert-success');
                                    return back();
                                } else {
                                    Session::flash('message', 'Client Data Update Failed! Contact Please');
                                    Session::flash('m-class', 'alert-danger');
                                    return back();
                                }
                            } else {
                                Session::flash('message', 'Client Payment Create Failed! Contact Please');
                                Session::flash('m-class', 'alert-danger');
                                return back();
                            }


                        }

                    } else {

                        $pay_id = ClientPayment::create($payment_inputs)->id;
                        if (!empty($pay_id)) {
                            $tr_inputs['pay_id'] = $pay_id;

                            if(!(Transaction::create($tr_inputs))){
                                Session::flash('message', 'Transaction Create Failed! Contact Please');
                                Session::flash('m-class', 'alert-danger');
                                return back();
                            }

                            if ($client->update($update_client)) {
                                $ac->update($ac_inputs);
                                if ($client->resellerId != null) {
                                    $reseller->update($reseller_data);
                                }
                                //========PAYMENT CONFIRMATION SMS=======
                                $deelkoSMS = new DeelkoSMS();
                                $deelkoSMS->sendSMS($client,'sms_payment',$paid);
                                Session::flash('message', 'Payment has been Completed Successfully');
                                Session::flash('m-class', 'alert-success');
                                return back();
                            } else {
                                Session::flash('message', 'Client Data Update Failed! Contact Please');
                                Session::flash('m-class', 'alert-danger');
                                return back();
                            }
                        } else {
                            Session::flash('message', 'Client Payment Create Failed! Contact Please');
                            Session::flash('m-class', 'alert-danger');
                            return back();
                        }
                    }
                }
            }
        }
    }

    public function resellerRecharge($id, $amount,$payment_info)
    {
        $reseller = Reseller::find($id);
        $pre_balance = $reseller->balance;
        $new_balance = $reseller->balance + $amount;

        $payment_inputs = [
            'resellerId' => $id,
            'recharge_amount' => $amount,
            'pre_balance' => $pre_balance,
//            'user_id' => $reseller->user->id
        ];

        $ac = Account::whereNull('resellerId')->where('account_type', 'bKash')->first();
        $account_balance = $ac->account_balance + $amount;

        $tr_inputs = [
            'account_id' => $ac->id,
            'tr_type' => 'Reseller Recharge Balance',
            'tr_category' => 'Income',
            'tr_amount' => $amount,
            'payer' => $reseller->resellerName,
            'cr' => $amount,
            'bkash_trxID' => $payment_info['trxID'],
            'user_id' => $reseller->user->id,
            'trans_date' => date('Y-m-d')
        ];

        $tr_id = Transaction::create($tr_inputs)->id;
        if (!empty($tr_id)) {
            $payment_inputs['tr_id'] = $tr_id;
            if (ResellerPayment::create($payment_inputs)) {
                $ac->update(['account_balance' => $account_balance]);
                $reseller->update( ['balance' => $new_balance]);

                Session::flash('message', 'Recharge has been Completed Successfully');
                Session::flash('m-class', 'alert-success');
                return back();
            } else {
                Session::flash('message', 'Recharge has been Failed!');
                Session::flash('m-class', 'alert-danger');
                return back();
            }

        } else {
            Session::flash('message', 'Recharge has been Failed!');
            Session::flash('m-class', 'alert-danger');
            return back();
        }

    }


}
