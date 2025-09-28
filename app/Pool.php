<?php

namespace App;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Pool extends Model
{
    use LogsActivity,SoftDeletes;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = Pool::withTrashed()->find($activity->subject_id)->value('pool_name');
        $activity->properties = $activity->properties->merge([
            'subject_info' => $_subject_info,
            'ip' => \Request::ip(),
        ]);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['pool_name', 'range_ip', 'server.server_name'])
            ->useLogName('pool')->logOnlyDirty();
    }

    protected $fillable = [
        'pool_name', 'range_ip', 'server_id'
    ];

    function server()
    {
        return $this->belongsTO(Server::class, 'server_id', 'id');
    }

    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

}
