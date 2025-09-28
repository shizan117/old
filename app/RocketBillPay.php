<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RocketBillPay extends Model
{
    use HasFactory;
    
    protected $fillable = ['refNo', 'api_username', 'api_password'];
}
