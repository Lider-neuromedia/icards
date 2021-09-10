<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'slug',
        'qr_code',
    ];

    public function client()
    {
        return $this->belongsTo(User::class);
    }

    public function fields()
    {
        return $this->hasMany(CardField::class);
    }

    public function getUrlAttribute()
    {
        return url("ec/{$this->slug}");
    }

    public function getVcardAttribute()
    {
        return url("storage/cards/card-{$this->slug}.vcf");
    }

    public function field($group, $key)
    {
        $field = $this->fields()
            ->where('group', $group)
            ->where('key', $key)
            ->first();
        return $field ? $field->value : '';
    }
}
