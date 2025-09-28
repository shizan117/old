<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BkashWebhook extends Model
{
    use HasFactory;
   protected  $table = "bkash_webhook_transactions";
   public $timestamps = false;
   protected $fillable = [
        'id', 'type', 'debitMSISDN', 'creditOrganizationName', 'creditShortCode', 'trxID', 'transactionStatus', 'transactionReference', 'user_check', 'transactionType', 'amount', 'currency', 'dateTime'
    ];
}
