<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'cards',
        'start_at',
        'finish_at',
        'notified_at',
    ];

    protected $dates = [
        'start_at',
        'finish_at',
        'notified_at',
    ];

    public function client()
    {
        return $this->belongsTo(User::class);
    }
}
