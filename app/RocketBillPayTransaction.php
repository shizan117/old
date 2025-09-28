<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RocketBillPayTransaction extends Model
{
    use HasFactory;
    protected $table = "rocket_bill_pay_transactions";
    protected $fillable = [
        'date',
        'username',
        'transaction_id',
        'rocket_transaction_id',
        'is_paid',
        'amount',
    ];
}
