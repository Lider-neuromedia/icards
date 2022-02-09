<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CardStatistic extends Model
{
    protected $fillable = [
        'action',
        'data',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
