<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ResellerPayment extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = ResellerPayment::find($activity->subject_id);
        $activity->properties = $activity->properties->merge([
            'subject_info' => "Recharge by - {$_subject_info->reseller->resellerName}",
            'ip' => \Request::ip(),
        ]);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['pre_balance', 'recharge_amount', 'tr_id', 'reseller.resellerName'])
            ->useLogName('reseller_payment')->logOnlyDirty();
    }

    protected $fillable = [
        'resellerId', 'pre_balance', 'recharge_amount', 'tr_id', 'user_id'
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'tr_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
