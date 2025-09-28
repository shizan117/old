<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Distribution extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = Distribution::find($activity->subject_id)->value('distribution');
        $activity->properties = $activity->properties->merge([
            'subject_info' => $_subject_info,
            'ip' => \Request::ip(),
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['distribution', 'branch.branchName', 'reseller.resellerName'])
            ->useLogName('distribution')->logOnlyDirty();
    }

    protected $fillable = [
        'distribution','branchId','resellerId'
    ];

    public $timestamps  = false;


    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branchId', 'branchId');
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'distribution_id', 'id');
    }

}


