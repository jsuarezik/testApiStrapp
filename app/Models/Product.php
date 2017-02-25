<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Product extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'price', 'in_stock'
    ];


    protected $table = 'product';

    protected $casts = [
        'in_stock' => 'boolean',
    ];

    public function users(){
        return $this->belongsToMany('App\Models\User');
    }
}
