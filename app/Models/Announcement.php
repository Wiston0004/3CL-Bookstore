<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id','campaign_id','title','body','channels',
        'target_type','target_value','scheduled_at','published_at','status',
        'send_count','fail_count',
    ];

    protected $casts = [
        'channels'=>'array','target_value'=>'array',
        'scheduled_at'=>'datetime','published_at'=>'datetime',
    ];

    public function event(){ return $this->belongsTo(Event::class); }
    public function campaign(){ return $this->belongsTo(Campaign::class); }
}
