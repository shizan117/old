<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invest extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'date', 'investor.name', 'user.name', 'tr_id', 'reseller.resellerName'])
            ->useLogName('invest')->logOnlyDirty();
    }

    protected $fillable = [
        'amount', 'date', 'investor_id', 'user_id', 'tr_id', 'branchName', 'resellerId'
    ];

    public $timestamps  = false;

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'tr_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

   public function investor()
    {
        return $this->belongsTo(Investor::class, 'investor_id', 'id');
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }
}
