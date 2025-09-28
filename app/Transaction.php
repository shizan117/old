<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Transaction extends Model
{
//    use LogsActivity;
//
//    public function getActivitylogOptions(): LogOptions
//    {
//        return LogOptions::defaults()
//            ->logOnly(['account.account_name', 'tr_type', 'tr_category',  'tr_amount', 'tr_vat','charge',
//                'payer', 'payee', 'dr',  'cr', 'user.name', 'pay_id','bkash_trxID', 'invoice_id',
//                'r_invoice_id','trans_date','branch.branchName', 'reseller.resellerName'])
//            ->useLogName('transaction')->logOnlyDirty();
//    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id', 'tr_type', 'tr_category',  'tr_amount', 'tr_vat','charge', 'payer', 'payee', 'dr',  'cr',
        'user_id', 'branchId', 'resellerId', 'pay_id','bkash_trxID', 'invoice_id', 'r_invoice_id','trans_date','PayTime'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branchId', 'branchId');
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    public function clientPayment()
    {
        return $this->belongsTo(ClientPayment::class, 'pay_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
   
}
