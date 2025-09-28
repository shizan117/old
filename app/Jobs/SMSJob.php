<?php

namespace App\Jobs;

use App\SMS\DeelkoSMS;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $client,$smsType;
    public function __construct($client, $smsType)
    {
        $this->client = $client;
        $this->smsType = $smsType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $deelkoSMS = new DeelkoSMS();
        $deelkoSMS->sendSMS($this->client, $this->smsType);
    }
}
