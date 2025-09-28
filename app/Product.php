<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'unit', 'productCategory.name', 'stock', 'used',
                'sold', 'lost', 'serial_type', 'refund', 'branch.branchName', 'reseller.resellerName'])
            ->useLogName('product')->logOnlyDirty();
    }

    protected $fillable = [
        'name', 'unit', 'cat_id', 'stock', 'used', 'sold', 'lost', 'serial_type', 'refund', 'branchId', 'resellerId'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branchId', 'branchId');
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'cat_id', 'id');
    }

    public function purchaseItem()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockItem()
    {
        return $this->hasMany(StockItem::class);
    }

    public function usedItem()
    {
        return $this->hasMany(UsedItem::class);
    }

    public function lostItem()
    {
        return $this->hasMany(LostItem::class);
    }

    public function soldItem()
    {
        return $this->hasMany(SoldItem::class);
    }

    public function refundItem()
    {
        return $this->hasMany(RefundItem::class);
    }
}
