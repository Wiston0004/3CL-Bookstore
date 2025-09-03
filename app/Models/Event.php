<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organizer_id','title','slug','description','type','delivery_mode',
        'timezone','starts_at','ends_at','venue_name','address','lat','lng',
        'join_url','visibility','target_segment_id','status','cancellation_reason',
        'registration_required','max_attendees','banner_path','points_reward',
    ];

    protected $casts = [
        'starts_at'=>'datetime','ends_at'=>'datetime',
        'lat'=>'decimal:7','lng'=>'decimal:7',
        'registration_required'=>'boolean','points_reward'=>'integer',
    ];

    public const DRAFT='draft';
    public const SCHEDULED='scheduled';
    public const LIVE='live';
    public const COMPLETED='completed';
    public const CANCELLED='cancelled';

    public function organizer(){ return $this->belongsTo(User::class, 'organizer_id'); }
    public function registrations(){ return $this->hasMany(EventRegistration::class); }
    public function campaigns(){ return $this->hasMany(Campaign::class); }

    public function getRouteKeyName(){ return 'slug'; } // route model binding by slug
}
