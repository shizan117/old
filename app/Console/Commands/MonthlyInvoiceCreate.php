<?php

namespace App\Console\Commands;

use App\Client;
use App\ClientPayment;
use App\Config;
use App\Invoice;
use App\Reseller;
use App\ResellerPlan;
use App\SMS\DeelkoSMS;
use Illuminate\Console\Command;
use Mail;
use App\Mail\CornMail;
use MikrotikAPI\Commands\IP\Firewall\FirewallAddressList;
use MikrotikAPI\Commands\IP\Hotspot\HotspotActive;
use MikrotikAPI\Commands\IP\Hotspot\HotspotUsers;
use MikrotikAPI\Commands\PPP\Active;
use MikrotikAPI\Commands\PPP\Secret;
use MikrotikAPI\Commands\Queues\Simple;
use MikrotikAPI\Roar\Roar;

class MonthlyInvoiceCreate extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'sms:invoiceCreate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Client Invoice Created';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */

	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Dhaka");
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$client_data = Client::where('server_status', 1)
		                     ->where('status', 'On')
		                     ->get();

		$client_data_array = array();
		if (count($client_data) > 0) {

            $invoice_system = Config::where('config_title', 'invoice_system')->first()->value;

            if($invoice_system == 'fixed') {
                foreach ($client_data as $client) {
                    if ($client->resellerId != null) {
                        $plan_price = ResellerPlan::where('plan_id', $client->plan_id)->where('resellerId', $client->resellerId)->value('reseller_sell_price');
                    } else {
                        $plan_price = $client->plan->plan_price;
                    }
                    if (($plan_price - $client->discount) > 0) {
                        $client_data_array[] = [
                            'client_id' => $client->id
                        ];
                    }
                }
            } else{
                $current_date = date("Y-m-d H:i:s");
                foreach ($client_data as $client) {
                    if ($current_date < $client->expiration) {
                        $exp_date = strtotime($client->expiration);
                        $c_date = strtotime($current_date);
                        $time_left = $exp_date - $c_date;
                        $daysleft = round($time_left / 86400);
                        if ($daysleft >= 0 && $daysleft < 4) {
                            if ($client->resellerId != null) {
                                $plan_price = ResellerPlan::where('plan_id', $client->plan_id)->where('resellerId', $client->resellerId)->value('reseller_sell_price');
                            } else {
                                $plan_price = $client->plan->plan_price;
                            }
                            if (($plan_price - $client->discount) > 0) {
                                $client_data_array[] = [
                                    'client_id' => $client->id
                                ];
                            }
                        }
                    }
                }
            }
		}

		if (count($client_data_array) > 0) {
			return $this->ClientInvoiceCreate($client_data_array);
		} else {
			return false;
		}

	}


	private function ClientInvoiceCreate($data = array())
	{
		if (count($data) > 0) {

			for ($i = 0; $i < count($data); $i++) {
			    if ($i > 0 && $i % 100 == 0) {
                    sleep(5);
                }
				$client_Data = Client::find($data[$i]['client_id']);
				$inv = Invoice::withTrashed()->where([
					['client_id', '=', $client_Data->id],
					['bill_month', '=', date('m')],
					['bill_year', '=', date('Y')]
				])->first();
				if (empty($inv)) {

                    $p_price = $client_Data->plan->plan_price;

                    if ($client_Data->resellerId != null) {
                        $reseller_plan = ResellerPlan::where([
                            ["plan_id", $client_Data->plan_id],
                            ["resellerId", $client_Data->resellerId]
                        ])->first();
                        $p_price = $reseller_plan->reseller_sell_price;
                    }

                    $discount = $client_Data->discount;
                    $charge = $client_Data->charge;
                    $sub_total = $p_price + $charge - $discount;
                    $pre_due = $client_Data->due;
                    $due = $pre_due + $sub_total;

                    $update_client = [
                        'due' => $due
                    ];

                    if ($client_Data->resellerId != null) {
                        $reseller = Reseller::find($client_Data->resellerId);
                        $vatRate = $reseller->vat_rate;
                        $buy_price = $reseller_plan->sell_price;

                        $rVatRate = \App\Config::where('config_title', 'vatRate')->first();

                        $rPrice = ceil(($buy_price * 100) / (100 + $rVatRate->value));
                        $r_vat = $buy_price - $rPrice;
                        $r_plan_price = $buy_price - $r_vat;

                        $return = 'true';

                        if ($buy_price <=0) {$return = 'false';}
                    } else {
                        $vatData = \App\Config::where('config_title', 'vatRate')->first();
                        $vatRate = $vatData->value;
                        $buy_price = 0;
                        $return = 'true';
                    }

                    $price = ceil((($sub_total) * 100) / (100 + $vatRate));
                    $vat = $sub_total - $price;
                    $plan_price = $p_price - $vat;

                    $inputs = [
                        'client_id' => $client_Data->id,
                        'plan_id' => $client_Data->plan->id,
                        'bandwidth' => $client_Data->plan->bandwidth->bandwidth_name,
                        'bill_month' => date('m'),
                        'bill_year' => date('Y'),
                        'buy_price' => $buy_price,
                        'plan_price' => $plan_price,
                        'service_charge' => $charge,
                        'total' => $plan_price,
                        'discount' => $discount,
                        'all_total' => $sub_total - $vat,
                        'vat' => $vat,
                        'sub_total' => $sub_total,
                        'paid_amount' => 0,
                        'due' => $sub_total,
                        'duration' => $client_Data->plan->duration,
                        'duration_unit' => $client_Data->plan->duration_unit,
                        'branchId' => $client_Data->branchId,
                        'resellerId' => $client_Data->resellerId
                    ];

                    if ($return == 'true') {
                        if ($client_Data->update($update_client)) {
                            $new_invoice = Invoice::create($inputs);
//                            if ($client_Data->resellerId != null) {
////                                $reseller->update($reseller_data);
//                            }
//							$company = Config::where('config_title', 'companyName')->first();
//							$message = new \StdClass();
////							$message->top_line = date('F-Y') ." Invoice Created For You. Your due balance ". $due ."<br>Please Pay Your Internet Bill Before ". $client_Data->expiration ."<br>Any Query Plz Contact Us.";
//                            $message->top_line = Config::where('config_title','sms_invoice')->value('value');
//							$message->companyName = $company->value;
//							$message->buttonText = 'Pay Now';
//							$message->clientName = $client_Data->client_name."(".$client_Data->username.")";
//							$message->url = route('client.pay', $new_invoice->id);
//							$subject = $company->value.' Invoice Created '.date('F-Y');
//							$name = 'Billing '.$company->value;

							//Mail::to($client_Data->email)->send(new CornMail($message, $subject, $name));

//								$mobile_no = $client_Data->phone;
//								$date = date('d M Y', strtotime($client_Data->expiration));
////								$sms_content = "Dear Customer (".$client_Data->username."),\n\n".date('F-Y') ." Internet Invoice Created For You. Your due ". $due ."Tk.\nPlease Pay Bill Before ". $date ."\n\nThanks,\n".$company->value."";
//                                $sms_content = Config::where('config_title','sms_invoice')->value('value');

                                $deelkoSMS = new DeelkoSMS();
                                $deelkoSMS->sendSMS($client_Data,'sms_invoice',null,$due);


                            //========== PAY FROM ADVANCE IF CLIENT HAS PREVIOUS ADVANCE BALANCE ============
                            $client = Client::find($data[$i]['client_id']);
                            if ($new_invoice != '' && $client->balance > 0) {

                                if ($client->balance > $new_invoice->sub_total) {
                                    $paid = $new_invoice->sub_total;
                                } else { $paid = $client->balance; }

                                $invoice_due = $new_invoice->due;
                                if ($new_invoice->paid_amount > 0) {
                                    $plan_price = 0.00;
                                    $service_charge = 0.00;
                                    $pre_due = $new_invoice->client->due;
                                    $vat = 0.00;
                                    $total = $pre_due;
                                    $discount = 0.00;
                                } else {
                                    $plan_price = $new_invoice->plan_price;
                                    $service_charge = $new_invoice->service_charge;
                                    $pre_due = $new_invoice->client->due - $invoice_due;
                                    $vat = $new_invoice->vat;
                                    $total = $new_invoice->total + $pre_due;
                                    $discount = $new_invoice->discount;
                                }

                                if ($client->due >= $paid) {
                                    if ($client->due >= $paid) {$due = $client->due - $paid;} else {$due = 0.00;}
                                } else { $due = 0.00; }

                                $new_balance = $client->balance - $paid;
                                $pre_balance = $client->balance;

                                $update_client = [
                                    'due' => $due,
                                    'balance' => $new_balance
                                ];

                                $inputs = [
                                    'paid_amount' => $new_invoice->paid_amount + $paid,
                                    'due' => $new_invoice->due - $paid
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

                                    if ($buy_price > $reseller->balance) {
                                        continue;
                                    }else{
                                        $reseller_data = [
                                            'balance' => $reseller->balance - $buy_price
                                        ];
                                    }

                                    $user_id = $reseller->user->id;
                                } else {
                                    $vatData = \App\Config::where('config_title', 'vatRate')->first();
                                    $vatRate = $vatData->value;
                                    $c_exp_value = \App\Config::where('config_title', 'exp_date')->first();
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
                                    $user_id = 1;
                                }

                                $sub_total = $total - $discount;
                                $payment_inputs = [
                                    'client_id' => $new_invoice->client_id,
                                    'bandwidth' => $new_invoice->bandwidth,
                                    'bill_month' => $new_invoice->bill_month,
                                    'bill_year' => $new_invoice->bill_year,
                                    'plan_price' => $plan_price,
                                    'advance_payment' => 0.00,
                                    'service_charge' => $service_charge,
                                    'pre_due' => $pre_due,
                                    'total' => $total,
                                    'discount' => $discount,
                                    'all_total' => $sub_total,
                                    'vat' => $vat,
                                    'sub_total' => $sub_total + $vat,
                                    'paid_amount' => $paid,
                                    'new_paid' => 0.00,
                                    'paid_from_advance' => $paid,
                                    'pre_balance' => $pre_balance,
                                    'due' => $due,
                                    'user_id' => $user_id,
                                    'payment_date' => date('Y-m-d')
                                ];

                                $exp_time = explode(':',setting('exp_time'));
                                if ($new_invoice['duration_unit'] == '2') {
                                    $date_exp = date("Y-m-d H:i:s", mktime($exp_time[0],$exp_time[1],'00', date("m"), date("d") + $new_invoice->duration, date("Y")));
                                } else {
                                    $date_exp = date("Y-m-d H:i:s", mktime($exp_time[0],$exp_time[1],'00', date("m") + $new_invoice->duration, date($c_exp_date), date("Y")));
                                }
                                $update_client['expiration'] = $date_exp;

                                if ($client->start_transaction == "") {
                                    $update_client ['start_transaction'] = date("Y-m-d");
                                }

                                if (\App\ClientPayment::create($payment_inputs)) {
                                    if ($client->update($update_client)) {
                                        $new_invoice->update($inputs);
                                        if ($client_Data->resellerId != null) {
                                            $reseller->update($reseller_data);
                                        }
                                    }
                                }
                                continue;


                            } //END OF ADVANCE PAYMENT

                        }
                    }

				}
			}

			return true;
		} else {
			return false;
		}
	}
}
