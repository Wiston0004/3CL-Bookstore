<?php

namespace App\Models;

use App\Jobs\AwardPointsForRegistration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EventRegistration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id','user_id','name','email','phone',
        'status','registered_at','checked_in_at','source','token',
        'awarded_points','awarded_at',
    ];

    protected $casts = [
        'registered_at'=>'datetime',
        'checked_in_at'=>'datetime',
        'awarded_at'=>'datetime',
    ];

    protected static function booted(){
        static::creating(function($m){
            if(empty($m->token)) $m->token = Str::random(32);
            if(empty($m->registered_at)) $m->registered_at = now();
        });
        // Command: award points after registration
        static::created(function($m){
            AwardPointsForRegistration::dispatch($m->id);
        });
    }

    public function event(){ return $this->belongsTo(Event::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
