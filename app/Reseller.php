<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Reseller extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = Reseller::find($activity->subject_id)->value('resellerName');
        $activity->properties = $activity->properties->merge([
            'subject_info' => $_subject_info,
            'ip' => \Request::ip(),
        ]);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'resellerName', 'resellerLocation', 'business_name', 'phone', 'logo', 'signature','prefix','notice',
                 'balance', 'c_exp_date',
                'bkash_username','bkash_password','bkash_app_key','bkash_app_secret','bkash_charge','bkash_url', 'bkash_production_root_url',
                'sms_api_url','sms_api_key','sms_masking_id','sms_is_active','sms_remainder','sms_payment','sms_disconnect','sms_invoice','sms_new_client',
                'nagad_merchant_id','nagad_merchant_number','nagad_pg_public_key','nagad_merchant_private_key','nagad_charge'
            ])
            ->useLogName('reseller')->logOnlyDirty();
    }
    protected $primaryKey   = 'resellerId';

    protected $fillable = [
        'resellerName', 'resellerLocation', 'business_name', 'phone', 'logo', 'signature','prefix','notice',
        'credit_limit', 'balance', 'due', 'exp_date', 'c_exp_date', 'vat_rate',
        'bkash_username','bkash_password','bkash_app_key','bkash_app_secret','bkash_charge','bkash_url', 'bkash_production_root_url',
        'sms_api_url','sms_api_key','sms_masking_id','sms_is_active','sms_remainder','sms_payment','sms_disconnect','sms_invoice','sms_new_client', 'sms_custom',
        'nagad_merchant_id','nagad_merchant_number','nagad_pg_public_key','nagad_merchant_private_key','nagad_charge', 'sms_secret_key', 'sms_client_id'
    ];

    public $timestamps  = false;


    public function clients()
    {
        return $this->hasMany('App\Client', 'resellerId', 'resellerId');
    }

    public function users()
    {
        return $this->hasMany('App\User', 'resellerId', 'resellerId');
    }

    public function user()
    {
        return $this->first()->belongsTo(User::class, 'resellerId', 'resellerId');
    }

    public function resellerPlan()
    {
        return $this->hasMany(ResellerPlan::class);
    }
    public function distributions()
    {
        return $this->hasMany('App\Distribution', 'resellerId', 'resellerId');
    }
    public function accounts()
    {
        return $this->hasMany('App\Account', 'resellerId', 'resellerId');
    }
}
