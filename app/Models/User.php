<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Model implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'is_admin'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    protected $table = 'user';

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setPasswordAttribute($pass)
    {
        $this->attributes['password'] = app('hash')->make($pass);
    }

    public function isAdmin(){
        return $this->is_admin ==  true;
    }

    /**
    * @Relation
    */
    public function created_tasks(){
        return $this->hasMany(Task::class,'creator_id');
    }

    /**
    * @Relation
    */
    public function assigned_tasks(){
        return $this->hasMany(Task::class,'assigned_user_id');
    }
}
