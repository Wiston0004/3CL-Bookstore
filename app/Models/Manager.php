<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Manager extends User
{
    protected $table = 'users';

    protected static function booted()
    {
        // Only manager rows in Manager::query()
        static::addGlobalScope('role_manager', function (Builder $q) {
            $q->where('role', 'manager');
        });

        // Ensure role is set on create
        static::creating(function ($model) {
            $model->role = 'manager';
        });
    }

    // Manager is immutable in your app — you can add guards here if needed
}
