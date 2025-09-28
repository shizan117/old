<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Config extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['config_title', 'value'])
            ->useLogName('settings')->logOnlyDirty();
    }

    protected $fillable = [
        'config_title', 'value'
    ];
}
