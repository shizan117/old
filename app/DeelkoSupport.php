<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeelkoSupport extends Model
{
    use HasFactory;
    protected $fillable = ['url_endpoint', 'youTube_share_link', 'select_end_point', 'update_youtube_link'];
}
