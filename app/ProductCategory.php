<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductCategory extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'reseller.resellerName'])
            ->useLogName('product_category')->logOnlyDirty();
    }

    protected $fillable = [
        'name', 'resellerId'
    ];

    public $timestamps  = false;

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }


    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
