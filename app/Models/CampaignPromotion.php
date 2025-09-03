<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignPromotion extends Model
{
    use SoftDeletes;

    protected $fillable = ['campaign_id','rule_type','rule_config','priority','stackable','active'];
    protected $casts = ['rule_config'=>'array','stackable'=>'boolean','active'=>'boolean'];

    public function campaign(){ return $this->belongsTo(Campaign::class); }
}
