<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Task;

class Priority extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    protected $table = 'priority';

    /**
    * @Relation
    */
    public function tasks() {
        return $this->hasMany(Task::class);
    }



}