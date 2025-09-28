<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Exceptions\ResetClientPasswordNotification;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Client extends Authenticatable
{
    use Notifiable, SoftDeletes, LogsActivity;

    // public function tapActivity(Activity $activity)
    // {
    //     $_subject_info = Client::withTrashed()->find($activity->subject_id);
    //     $activity->properties = $activity->properties->merge([
    //         'subject_info' => "{$_subject_info->client_name}-({$_subject_info->username})",
    //         'ip' => \Request::ip(),
    //     ]);
    // }
    public function tapActivity(Activity $activity)
{
    // Fetch the client record, including soft-deleted ones
    $_subject_info = Client::withTrashed()->find($activity->subject_id);

    // Check if the client record exists
    if ($_subject_info) {
        // Merge properties if the client record exists
        $subject_info = "{$_subject_info->client_name}-({$_subject_info->username})";
    } else {
        // Set a default value if the client record does not exist
        $subject_info = 'Client record not found';
    }

    // Merge properties with subject info
    $activity->properties = $activity->properties->merge([
        'subject_info' => $subject_info,
        'ip' => \Request::ip(),
    ]);
}


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'client_name', 'username', 'email', 'password', 'phone', 'house_no', 'road_no', 'address', 'thana', 'district', 'user_image', 'clientNid', 'active', 'last_login', 'server_password',
                'plan.plan_name','plan_changed_at', 'start_transaction', 'expiration', 'server_status', 'server_active_date', 'server_inactive_date', 'status', 'due',
                'balance', 'branch.branchName', 'reseller.resellerName', 'client_ip', 'discount', 'charge','otc_charge', 'note', 'distribution.distribution', 'type_of_connection', 'type_of_connectivity', 'olt_type','cable_type'
            ])
            ->useLogName('client')->logOnlyDirty();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_name', 'username', 'email', 'password', 'phone', 'house_no', 'road_no', 'address', 'thana', 'district', 'user_image', 'clientNid', 'active', 'last_login', 'server_password',
        'plan_id','plan_changed_at', 'start_transaction', 'expiration', 'server_status', 'server_active_date', 'server_inactive_date', 'status', 'due',
        'balance', 'branchId', 'resellerId', 'client_ip', 'discount', 'charge','otc_charge', 'note', 'distribution_id', 'type_of_connection', 'type_of_connectivity', 'cable_type','client_photo', 'olt_type','other_documents',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    function plan(){
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branchId', 'branchId');
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }

    public function distribution()
    {
        return $this->belongsTo(Distribution::class, 'distribution_id', 'id');
    }

    public function invoices(){
        return $this->hasMany(Invoice::class);
    }

    public function clientPayment(){
        return $this->hasMany(ClientPayment::class);
    }


    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetClientPasswordNotification($token));
    }
}
