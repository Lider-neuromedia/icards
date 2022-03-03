<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $fillable = [
        'name',
    ];

    public function clients()
    {
        return $this->belongsToMany(User::class, 'client_seller', 'seller_id', 'client_id');
    }
}
