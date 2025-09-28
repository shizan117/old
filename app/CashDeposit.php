<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashDeposit extends Model
{
    protected $fillable = [
        'name', 'cat_name', 'amount', 'date', 'loan_payer_id', 'investor_id', 'user_id', 'tr_id', 'branchName', 'resellerId'
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

    public function investor()
    {
        return $this->belongsTo(Investor::class, 'investor_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branchId', 'branchId');
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }
}
