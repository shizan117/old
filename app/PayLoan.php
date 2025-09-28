<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayLoan extends Model
{
    protected $fillable = [
        'pay_amount', 'date', 'loan_payer_id', 'user_id', 'tr_id', 'resellerId'
    ];

    public $timestamps  = false;

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'tr_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function loanPayer()
    {
        return $this->belongsTo(LoanPayer::class, 'loan_payer_id', 'id');
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }
}
