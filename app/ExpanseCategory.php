<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ExpanseCategory extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = ExpanseCategory::find($activity->subject_id)->value('name');
        $activity->properties = $activity->properties->merge([
            'subject_info' => $_subject_info,
            'ip' => \Request::ip(),
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'reseller.resellerName'])
            ->useLogName('expense_category')->logOnlyDirty();
    }

    protected $fillable = [
        'name', 'resellerId'
    ];

    public $timestamps  = false;


    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }
}
