<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Exceptions\ResetAdminPasswordNotification;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable,LogsActivity, HasRoles;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = User::find($activity->subject_id)->value('name');
        $activity->properties = $activity->properties->merge([
            'subject_info' => $_subject_info,
            'ip' => \Request::ip(),
        ]);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([ 'name', 'email', 'password', 'phone', 'user_image', 'active', 'branch.branchName', 'reseller.resellerName'])
            ->useLogName('user')->logOnlyDirty();
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'user_image', 'active', 'branchId', 'resellerId',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branchId', 'branchId');
    }

    public function reseller()
    {
        return $this->belongsTo('App\Reseller', 'resellerId', 'resellerId');
    }


    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetAdminPasswordNotification($token));
    }

    public function complains()
    {
        return $this->hasMany(Complain::class,'assign_to','id');
    }
}
