<?php

namespace App\Console\Commands;

use App\Client;
use App\Config;
use App\SMS\DeelkoSMS;
use Illuminate\Console\Command;
use Mail;
use App\Mail\CornMail;

class MonthlyPaymentRemainder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:paymentRemainder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monthly Payment Notification';


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
                if ($current_date < $client->expiration && ($client->plan->plan_price - $client->discount) > 0 && $client->due > 0) {
                    $exp_date = strtotime($client->expiration);
                    $c_date = strtotime($current_date);
                    $time_left = $exp_date - $c_date;
                    $daysleft = round($time_left/86400);
                    if($daysleft >= 3 && $daysleft < 4) {
                        $client_data_array[] = [
                            'client_id'    => $client->id
                        ];
                    }
                }
            }
        }

        if(count($client_data_array) > 0){
            return [
                //$this->SendRemainderMail($client_data_array), 
                $this->SendReminderSms($client_data_array)
            ];
        }else{
            return false;
        }

    }

    private function SendRemainderMail($data = array())
    {
        if (count($data) > 0) {
            for ($i = 0; $i < count($data); $i++) {
                $company = Config::where('config_title', 'companyName')->first();
                $client_Data = Client::find($data[$i]['client_id']);
                $message = new \StdClass();
//                $message->top_line = "Your monthly internet bill payment date come to close 3 days remaining. Your Due ".$client_Data->due.".<br>Any Query Plz Contact Us.";
                $message->top_line = Config::where('config_title','sms_remainder')->value('value');
                $message->companyName = $company->value;
                $message->buttonText = 'Check Invoices';
                $message->clientName = $client_Data->client_name."(".$client_Data->username.")";
                $message->url = route('client.invoice');
                $subject = $company->value.' Payment Remainder '.date('F-Y');
                $name = 'Billing '.$company->value;

                Mail::to($client_Data->email)->send(new CornMail($message, $subject, $name));
            }
            return true;
        } else {
            return false;
        }
    }

    private function SendReminderSms($data = array())
    {
        foreach ($data as $client_data) {
            $client = Client::find($client_data['client_id']);
    //                $company = Config::where('config_title', 'companyName')->first();
    //                $mobile_no = $client->phone;
    ////                $sms_content = "Dear Customer (".$client->username."),\n\nYour bill payment date 3 days remaining. Your Due ".$client->due."Tk.\nAny Query Plz Contact Us.\n\nThanks,\n".$company->value."";
    //                $sms_content = Config::where('config_title','sms_remainder')->value('value');

            $deelkoSMS = new DeelkoSMS();
            $deelkoSMS->sendSMS($client,'sms_remainder');

        }
        return true;
    }


}