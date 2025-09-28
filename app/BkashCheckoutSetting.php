<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BkashCheckoutSetting extends Model
{
    use HasFactory;

    protected $table = "bkash_checkout_settings";

    protected $fillable = [
        'product_name',
        'username',
        'password',
        'app_key',
        'app_secret',
        'grant_token_api_endpoint',
        'create_payment_api_endpoint',
        'execute_payment_api_endpoint',
        'query_payment_api_endpoint',
        'search_transaction_payment_api_endpoint',
        'resellerId'
    ];
}
