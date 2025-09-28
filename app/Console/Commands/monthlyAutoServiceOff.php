<?php

namespace App\Console\Commands;

use App\Client;
use App\Config;
use App\SMS\DeelkoSMS;
use Illuminate\Console\Command;
use MikrotikAPI\Roar\Roar,
    MikrotikAPI\Commands\PPP\Secret,
    MikrotikAPI\Commands\PPP\Active,
    MikrotikAPI\Commands\IP\Hotspot\HotspotUsers,
    MikrotikAPI\Commands\IP\Hotspot\HotspotActive,
    MikrotikAPI\Commands\IP\Firewall\FirewallAddressList,
    MikrotikAPI\Commands\Queues\Simple;
use Mail;
use App\Mail\CornMail;
class monthlyAutoServiceOff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:internetServiceOff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Client Internet service automatic off after expired payment date';

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
            $current_date = date("Y-m-d H:i:s");
            foreach ($client_data as $client) {
                if ($current_date > $client->expiration && ($client->plan->plan_price - $client->discount) > 0) {
                    $client_data_array[] = [
                        'client_id' => $client->id
                    ];
                }
            }
        }

        if (count($client_data_array) > 0) {
            return [
                $this->DeactivateClientIspServer($client_data_array),
                //$this->SendInactiveInfoMail($client_data_array),
                $this->SendInactiveInfoSms($client_data_array)
            ];
        } else {
            return false;
        }
    }


    private function SendInactiveInfoSms($data = array())
    {
        foreach ($data as $client_data) {
            $client = Client::find($client_data['client_id']);
//            $company = Config::where('config_title', 'companyName')->first();
//              $sms_content = "Dear Customer (".$client->username."),\n\nYour payment date expired. So your internet line is disconnect.\nAny Query Contact Us.\n\nThanks\n".$company->value."";

            $deelkoSMS = new DeelkoSMS();
            $deelkoSMS->sendSMS($client,'sms_disconnect');
        }
        return true;
    }


    private function DeactivateClientIspServer($data = array())
    {
        if (count($data) > 0) {
            for ($i = 0; $i < count($data); $i++) {
                $client_Data = Client::find($data[$i]['client_id']);
                $inputs['status'] = 'Off';

                if(setting('using_mikrotik')) {
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
                                $delActive = new Active($con);
                                $oldActiveId = $delActive->getId($client_Data->username);
                                if ($updateToServer->set($clientData, $serverUserId) && $delActive->delete($oldActiveId)) {
                                    $return = true;
                                } else {
                                    $return = false;
                                }
                            } elseif ($client_Data->plan->type == 'Hotspot') {
                                $delActive = new HotspotActive($con);
                                $oldActiveId = $delActive->getId($client_Data->username);
                                if ($updateToServer->set($clientData, $serverUserId) && $delActive->delete($oldActiveId)) {
                                    $return = true;
                                } else {
                                    $return = false;
                                }
                            } else {
                                $firewall_address = new FirewallAddressList($con);
                                $fire_ip_data = [
                                    'address' => $client_Data->client_ip,
                                    'list' => 'Redirect IP'
                                ];

                                if ($firewall_address->add($fire_ip_data)) {
                                    $return = true;
                                } else {
                                    $return = false;
                                }
                            }

                            if ($return == true) {
                                $client_Data->update($inputs);
                            }
                        }
                        else{
                            $inputs['server_status'] = 2;
                            $client_Data->update($inputs);
                        }
                    }
                } else {

                    $client_Data->update($inputs);

                }
            }
            return true;
        } else{
            return false;
        }
    }

    private function SendInactiveInfoMail($data = array())
    {
        if (count($data) > 0) {
            for ($i = 0; $i < count($data); $i++) {

                $company = Config::where('config_title', 'companyName')->first();
                $client_Data = Client::find($data[$i]['client_id']);
                $message = new \StdClass();
//                $message->top_line = "Your monthly internet bill payment date expired, and your due ". $client_Data->due .". So your internet line is disconnect.<br>Any Query Plz Contact Us.";
                $message->top_line = Config::where('config_title','sms_disconnect')->value('value');
                $message->companyName = $company->value;
                $message->buttonText = 'Check Invoices';
                $message->clientName = $client_Data->client_name."(".$client_Data->username.")";
                $message->url = route('client.invoice');
                $subject = $company->value.' Internet Line Is Disconnect';
                $name = 'Billing '.$company->value;

                Mail::to($client_Data->email)->send(new CornMail($message, $subject, $name));

            }
            return true;
        } else {
            return false;
        }
    }

}
