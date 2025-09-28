<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class UsedItem extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['product.name', 'qty','price', 'total_price', 'serial', 'comment'])
            ->useLogName('used_item');
    }
    protected $fillable = [
        'product_id', 'qty','price', 'total_price', 'serial', 'comment'
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class, 'stock_id', 'id');
    }
}
