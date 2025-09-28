<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResellerInvoice extends Model
{

    protected $fillable = [
        'resellerId', 'bill_month', 'bill_year', 'buy_price', 'discount', 'total', 'vat', 'sub_total', 'paid_amount', 'due'
    ];

    function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }
}
