<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Complain extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = Complain::find($activity->subject_id);
        $activity->properties = $activity->properties->merge([
            'subject_info' => "Complain by - {$_subject_info->client->client_name} ({$_subject_info->client->username})",
            'ip' => \Request::ip(),
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'client.client_name', 'title', 'complain_date','solved_date','description','assignTo.name'
            ])
            ->useLogName('complain')->logOnlyDirty();
    }

    protected $fillable = [
        'client_id', 'title', 'complain_date','solved_date','description'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function assignTo()
    {
        return $this->belongsTo(User::class, 'assign_to', 'id');
    }

}
