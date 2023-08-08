<?php

namespace App;

use App\Enums\GroupField;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'slug',
        'slug_number',
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

    public function getUrlNumberAttribute()
    {
        return url("{$this->client->slug}/{$this->slug_number}");
    }

    public function getVcardAttribute()
    {
        return url("storage/cards/card-{$this->slug}.vcf");
    }

    public function getNameAttribute()
    {
        return $this->field(GroupField::OTHERS, 'name') ?: '';
    }

    public function getEmailAttribute()
    {
        return $this->field(GroupField::ACTION_CONTACTS, 'email') ?: '';
    }

    public function field($group, $key, $withMarkdown = false)
    {
        $field = $this->fields()
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        if ($field == null) {
            $field_value = '';

            foreach (CardField::TEMPLATE_FIELDS[$group]['values'] as $value) {
                if ($value['key'] == $key) {
                    $isJson = $value['type'] == "gradient";
                    $isMarkdown = $value['type'] == "textarea";

                    if ($isJson) {
                        $field_value = json_decode($value['default']);
                    } elseif ($isMarkdown && $withMarkdown) {
                        $field_value = markdown($value['default']);
                    } else {
                        $field_value = $value['default'];
                    }
                }
            }

            return $field_value;
        }

        $isMarkdown = $field->type == "textarea";
        $isJson = $field->type == "gradient";

        if ($isJson) {
            return json_decode($field->value);
        } elseif ($isMarkdown && $withMarkdown) {
            return markdown($field->value);
        }

        return $field->value;
    }
}
