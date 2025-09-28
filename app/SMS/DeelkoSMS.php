<?php

namespace App\SMS;

use App\Config;
use App\Reseller;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeelkoSMS
{

    /**
     * Create a new message instance.
     *
     * @return void
     */
    //    public $message;
    //    public $to;
    //    public $sms_config;

    public function __construct() {}

    public function sendSMS($client, $message_type, $paid = null, $due = null, $password = null, $resellerPayment = null)
    {
        // dd($resellerPayment);

        if ($client->resellerId != '' && $resellerPayment != true) {
            $r = Reseller::find($client->resellerId);
            $sms_config = $r;
        } else {
            $config = Config::all();
            foreach ($config as $value) {
                $result[$value['config_title']] = $value['value'];
            }
            $sms_config = (object)$result;
        }

        //    	echo "<pre>";
        //		print_r($client);die;
        // dd( DB::table('users')->where('resellerId', $client->resellerId)->value('phone'));

        if ($sms_config->sms_is_active && $sms_config->sms_api_key != '') {

            // $mobile_no = '';
            // if($client->phone){
            //     $mobile_no = $client->phone;
            // }elseif($resellerPayment != true){
            //     $mobile_no = $client->phone;
            // }else{
            //     $mobile_no = DB::table('users')->where('resellerId', $client->resellerId)->value('phone');
            // }

            $mobile_no = $client->phone ?? null;

            if (empty($mobile_no) && $resellerPayment) {
                $mobile_no = DB::table('users')
                    ->where('resellerId', $client->resellerId)
                    ->value('phone');
            }

            if ($client->resellerId != '' && $resellerPayment != true) {
                $message = $r->$message_type;
            } else {
                $message = Config::where('config_title', $message_type)->value('value');
            }
            if ($mobile_no != '' && $message != '') {

                $replaceArr = [
                    "[name]" => $client->client_name ? $client->client_name : $client->resellerName,
                    "[username]" =>  $client->username ? $client->username : '',
                    "[expiration]" => $client->expiration ? date('d-M-y', strtotime($client->expiration)) : '',
                    "[due]" => $client->username ? ($due ?? $client->due) : '',
                    "[paid]" =>  $paid,
                    "[password]" =>  $password,
                ];

                $message = strtr($message, $replaceArr);

                $lenght = strlen((string)$mobile_no);
                if ($lenght > 11) {
                    $to = substr_replace($mobile_no, "880", 0, 3) . "";
                } else if ($lenght == 11) {
                    $to = substr_replace($mobile_no, "880", 0, 1) . "";
                } else {
                    $to = substr_replace($mobile_no, "880", 0, 0) . "";
                }
                // $content = 'apikey=' . $sms_config->sms_api_key .
                //            '&smsType=text'.
                //            '&toUser=' . $to .
                //            '&messageContent=' . urlencode($message);
                // if($sms_config->sms_masking_id != ''){
                //     $content .= '&callerID='.urlencode($sms_config->sms_masking_id);
                // }

                // $SMS_API= $sms_config->sms_api_url .'?'. $content;

                // Hardcoded secret key
                $secretkey = $sms_config->sms_secret_key;

                // Prepare the content for the API request
                $content = 'apikey=' . $sms_config->sms_api_key .
                    '&secretkey=' . $secretkey .           // Add the hardcoded secret key
                    '&smsType=text' .                      // Assuming it's always text, as in your previous config
                    '&toUser=' . $to .                     // Recipient's number
                    '&messageContent=' . urlencode($message); // The SMS content

                // Check if callerID (masking ID) is set, and append it if available
                if ($sms_config->sms_masking_id != '') {
                    $content .= '&callerID=' . urlencode($sms_config->sms_masking_id); // Append callerID if available
                }

                // Combine the base API URL and the parameters
                // $SMS_API = $sms_config->sms_api_url . '?' . $content;
                $SMS_API = env('SMS_API_URL') . '?' . $content;

                logger('SMS API URL: ' . $SMS_API);

                // dd($SMS_API);

                // $SMS_API is now ready to be used for hitting the API


                //                echo $SMS_API;die;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $SMS_API);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_VERBOSE, 0);
                $r = curl_exec($ch);
                curl_close($ch);
                //			die;


                //                // WEB71 Ultimate SMS (24smsbd.com)
                //                $url = $sms_config->sms_api_url;
                //                $data = array('sender_id' => $sms_config->sms_masking_id,
                //                    'apiKey' => $sms_config->sms_api_key,
                //                    'mobileNo' => $mobile_no,
                //                    'message' =>$message
                //                );
                //                // print_r($data);die;
                //                $curl = curl_init($url);
                //                curl_setopt($curl, CURLOPT_POST, true);
                //                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                //                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                //                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                //                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                //                curl_exec($curl);
                //                curl_close($curl);
            }
        }
    }
}
