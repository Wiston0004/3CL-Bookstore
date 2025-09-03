<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id','name','slug','description','starts_at','ends_at','status',
        'visibility','target_segment_id','notes',
    ];

    protected $casts = ['starts_at'=>'datetime','ends_at'=>'datetime'];

    public function event(){ return $this->belongsTo(Event::class); }
    public function promotions(){ return $this->hasMany(CampaignPromotion::class); }
}
