<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Income extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = Income::find($activity->subject_id)->value('name');
        $activity->properties = $activity->properties->merge([
            'subject_info' => $_subject_info,
            'ip' => \Request::ip(),
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'category.name', 'amount', 'note', 'date', 'tr_id',  'branch.branchName', 'reseller.resellerName'])
            ->useLogName('income')->logOnlyDirty();
    }


    protected $fillable = [
        'name', 'cat_id', 'amount', 'note', 'date', 'user_id', 'tr_id', 'branchId', 'resellerId'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'tr_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(IncomeCategory::class, 'cat_id', 'id');
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
