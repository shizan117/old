<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseItem extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['product.name', 'qty','price', 'total_price', 'purchase_id'])
            ->useLogName('purchase_item')->logOnlyDirty();
    }

    protected $fillable = [
        'product_id', 'qty','price', 'total_price', 'purchase_id'
    ];

    public $timestamps  = false;

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
