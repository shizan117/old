<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Purchase extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['price', 'purchase_date', 'tr_id', 'user.name', 'branch.branchName', 'reseller.resellerName'])
            ->useLogName('purchase')->logOnlyDirty();
    }
    protected $fillable = [
        'price', 'purchase_date', 'tr_id', 'user_id', 'branchId', 'resellerId'
    ];

    public $timestamps  = false;

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branchId', 'branchId');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'tr_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function purchaseItem()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
