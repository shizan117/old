<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanPayer extends Model
{
    protected $fillable = [
        'name', 'loan_amount', 'pay_amount', 'remain', 'resellerId'
    ];

    public $timestamps  = false;

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }
}
