<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use function Illuminate\Session\ipAddress;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Account extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = Account::find($activity->subject_id)->value('account_name');
        $activity->properties = $activity->properties->merge([
            'subject_info' => $_subject_info,
            'ip' => \Request::ip(),
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['account_name', 'account_type','account_number','account_balance',
                'branch.branchName', 'reseller.resellerName'])
            ->useLogName('account')->logOnlyDirty();
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_name', 'account_type', 'account_number',  'account_balance', 'branchId', 'resellerId'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branchId', 'branchId');
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }
}
