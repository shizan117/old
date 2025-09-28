<?php

namespace App;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Server extends Model
{
    use LogsActivity,SoftDeletes;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = Server::withTrashed()->find($activity->subject_id)->value('server_name');
        $activity->properties = $activity->properties->merge([
            'subject_info' => $_subject_info,
            'ip' => \Request::ip(),
        ]);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['server_name', 'server_ip', 'server_port', 'username', 'password'])
            ->useLogName('server')->logOnlyDirty();
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'server_name', 'server_ip', 'server_port', 'username', 'password'
    ];

    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    public function pools()
    {
        return $this->hasMany(Pool::class);
    }
}
