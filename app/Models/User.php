<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    /** Map role → subclass */
    public const ROLE_CLASS = [
        'manager'  => \App\Models\Manager::class,
        'staff'    => \App\Models\Staff::class,
        'customer' => \App\Models\Customer::class,
    ];

    protected $table = 'users';

    protected $fillable = [
        'name','username','email','password','role','phone','address','avatar_path','points'
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts  = [
        'email_verified_at' => 'datetime',
        'points'            => 'integer',
        // (optional alternative to the mutator): 'password' => 'hashed',
    ];

    // secure: auto-hash if plain given (keep if you don't use the 'hashed' cast)
    public function setPasswordAttribute($value){
        if ($value) $this->attributes['password'] = bcrypt($value);
    }

    public function isManager(){ return $this->role === 'manager'; }
    public function isStaff(){   return $this->role === 'staff'; }
    public function isCustomer(){return $this->role === 'customer'; }

    /**
     * STI magic: whenever Eloquent builds a model from DB,
     * re-instantiate the proper subclass based on 'role'.
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $attr = (object) $attributes;

        // If there's a role and a mapped class, return that subclass instance
        if (isset($attr->role) && isset(static::ROLE_CLASS[$attr->role])) {
            $class = static::ROLE_CLASS[$attr->role];

            /** @var \App\Models\User $instance */
            $instance = (new $class)->newInstance([], true);
            $instance->setRawAttributes((array) $attr, true);
            $instance->setConnection($connection ?: $this->getConnectionName());

            return $instance;
        }

        // Fallback: base User
        return parent::newFromBuilder($attributes, $connection);
    }
}
