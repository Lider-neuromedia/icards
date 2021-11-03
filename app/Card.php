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
        return url("{$this->client->slug}/{$this->slug}");
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

        if ($field == null) {
            $field_value = '';

            foreach (CardField::TEMPLATE_FIELDS[$group]['values'] as $value) {
                if ($value['key'] == $key) {
                    $field_value = $value['default'];
                }
            }

            return $field_value;
        }

        return $field->value;
    }
}
