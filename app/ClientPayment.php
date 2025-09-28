<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ClientPayment extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = ClientPayment::find($activity->subject_id);
        $activity->properties = $activity->properties->merge([
            'subject_info' => "Payment of {$_subject_info->client->client_name} ({$_subject_info->client->username})",
            'ip' => \Request::ip(),
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'client.client_name', 'bandwidth', 'bill_month', 'bill_year', 'plan_price', 'service_charge','otc_charge',
                'advance_payment', 'pre_due', 'total','discount',
                'vat', 'sub_total', 'paid_amount', 'new_paid', 'paid_from_advance', 'pre_balance',
                'due', 'tr_id', 'user_id','payment_date','branch.branchName', 'reseller.resellerName'
            ])
            ->useLogName('client_payment')->logOnlyDirty();
    }

    protected $fillable = [
        'client_id', 'bandwidth', 'bill_month', 'bill_year', 'plan_price', 'service_charge','otc_charge',
        'advance_payment', 'pre_due', 'total','discount', 'all_total',
        'vat', 'sub_total', 'paid_amount', 'new_paid', 'paid_from_advance', 'pre_balance',
        'due', 'tr_id', 'user_id','payment_date','branchId', 'resellerId'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'tr_id', 'id');
    }
    public function onlineTransaction()
    {
        return $this->belongsTo(Transaction::class, 'id', 'pay_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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
