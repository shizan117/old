<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Branch extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = Account::find($activity->subject_id)->value('branchName');
        $activity->properties = $activity->properties->merge([
            'subject_info' => $_subject_info,
            'ip' => \Request::ip(),
        ]);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['branchName', 'branchLocation'])
            ->useLogName('branch')->logOnlyDirty();
    }

    protected $primaryKey   = 'branchId';

    protected $fillable = [
        'branchName', 'branchLocation'
    ];

    public $timestamps  = false;


    public function clients()
    {
        return $this->hasMany('App\Client', 'branchId', 'branchId');
    }

    public function plans()
    {
        return $this->hasMany('App\Plan', 'branchId', 'branchId');
    }


    public function users()
    {
        return $this->hasMany('App\User', 'branchId', 'branchId');
    }

    public function distributions()
    {
        return $this->hasMany('App\Distribution', 'branchId', 'branchId');
    }
}
