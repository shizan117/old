<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Bandwidth extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = Bandwidth::find($activity->subject_id)->value('bandwidth_name');
        $activity->properties = $activity->properties->merge([
            'subject_info' => $_subject_info,
            'ip' => \Request::ip(),
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['bandwidth_name', 'rate_down','rate_down_unit','rate_up','rate_up_unit', 'bandwidth_allocation_mb'])
            ->useLogName('bandwidth')->logOnlyDirty();
    }

    protected $fillable = [
        'bandwidth_name', 'rate_down', 'rate_down_unit', 'rate_up', 'rate_up_unit', 'bandwidth_allocation_mb'
    ];

    public function plans()
    {
        return $this->hasMany(Plan::class);
    }
}
