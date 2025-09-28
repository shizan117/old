<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;


class ResellerPlan extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = ResellerPlan::find($activity->subject_id);
        if(!empty($_subject_info)) {
            $activity->properties = $activity->properties->merge([
                'subject_info' => "Reseller Plan of - {$_subject_info->reseller->resellerName}",
                'ip' => \Request::ip(),
            ]);
        }
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([ 'plan.plan_name', 'sell_price', 'reseller_sell_price', 'reseller.resellerName'])
            ->useLogName('reseller_plan')->logOnlyDirty();
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'resellerId', 'plan_id', 'sell_price', 'reseller_sell_price'
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }
}
