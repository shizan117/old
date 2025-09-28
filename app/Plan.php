<?php

namespace App;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;


class Plan extends Model
{
    use LogsActivity;

    use LogsActivity,SoftDeletes;
    
    public function tapActivity(Activity $activity)
    {
        $_subject_info = Plan::withTrashed()->find($activity->subject_id)->value('plan_name');
        $activity->properties = $activity->properties->merge([
            'subject_info' => $_subject_info,
            'ip' => \Request::ip(),
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['plan_name', 'type', 'plan_price', 'duration', 'duration_unit',
                'shared_users', 'server.server_name', 'bandwidth.bandwidth_name', 'pool.pool_name', 'branch.branchName'])
            ->useLogName('plan')->logOnlyDirty();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plan_name', 'type', 'plan_price', 'duration', 'duration_unit', 'shared_users', 'server_id', 'bandwidth_id', 'pool_id', 'branchId'
    ];

    public function bandwidth()
    {
        return $this->belongsTo(Bandwidth::class, 'bandwidth_id', 'id');
    }

    public function pool()
    {
        return $this->belongsTo(Pool::class, 'pool_id', 'id');
    }

    function server()
    {
        return $this->belongsTo(Server::class, 'server_id', 'id');
    }

    function branch()
    {
        return $this->belongsTo(Branch::class, 'branchId', 'branchId');
    }

    function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function resellerPlan()
    {
        return $this->hasMany(ResellerPlan::class);
    }
}
