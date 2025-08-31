<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title','author','isbn','description','stock','price_cents','cover_path',
    ];

    protected $casts = [
        'stock' => 'integer',
        'price_cents' => 'integer',
    ];

    public function categories() { return $this->belongsToMany(Category::class); }
    public function tags()       { return $this->belongsToMany(Tag::class); }
    public function reviews()    { return $this->hasMany(Review::class); }
    public function stockMovements() { return $this->hasMany(StockMovement::class); }

    // helpers
    public function getPriceAttribute() { return $this->price_cents / 100; }
    public function setPriceAttribute($value) { $this->attributes['price_cents'] = (int) round($value * 100); }
}
