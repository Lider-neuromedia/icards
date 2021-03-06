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

    public function statistics()
    {
        return $this->hasMany(CardStatistic::class);
    }

    public function getVisitsAttribute()
    {
        $visits = $this->statistics()->where('action', 'visit-card')->first();
        return $visits != null ? intval($visits->data) : 0;
    }

    public function getQrVisitsAttribute()
    {
        $visits = $this->statistics()->where('action', 'scan-card')->first();
        return $visits != null ? intval($visits->data) : 0;
    }

    public function getUrlAttribute()
    {
        return url("{$this->client->slug}/{$this->slug}");
    }

    public function getVcardAttribute()
    {
        return url("storage/cards/card-{$this->slug}.vcf");
    }

    public function getNameAttribute()
    {
        return $this->field('others', 'name') ?: '';
    }

    public function getEmailAttribute()
    {
        return $this->field('action_contacts', 'email') ?: '';
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
                    if ($value['type'] == "gradient") {
                        $field_value = json_decode($value['default']);
                    } else {
                        $field_value = $value['default'];
                    }
                }
            }

            return $field_value;
        }

        if ($field->type == "gradient") {
            return json_decode($field->value);
        }

        return $field->value;
    }
}
