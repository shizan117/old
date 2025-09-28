<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Investor extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'amount', 'reseller.resellerName'])
            ->useLogName('investor')->logOnlyDirty();
    }
    protected $fillable = [
        'name', 'amount', 'resellerId'
    ];

    public $timestamps  = false;

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }
}
