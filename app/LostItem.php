<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LostItem extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['product_id', 'qty','price', 'total_price', 'serial', 'comment'])
            ->useLogName('lost_item')->logOnlyDirty();
    }

    protected $fillable = [
        'product_id', 'qty','price', 'total_price', 'serial', 'comment'
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
