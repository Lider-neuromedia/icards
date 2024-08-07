<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Enums\FieldType;
use App\Enums\GroupField;

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

    public function scopeSearch($query, string $search)
    {
        $query->where(function ($q) use ($search) {
            $q->where('slug', 'like', "%$search%")
                ->orWhereHas('fields', function ($q) use ($search) {
                    $q->where('group', GroupField::OTHERS)
                        ->where('key', 'name')
                        ->where('value', 'like', "%$search%");
                });
        });
    }

    public function getVisitsAttribute(): int
    {
        $visits = $this->statistics()
            ->where('action', 'visit-card')
            ->first();
        return $visits != null ? intval($visits->data) : 0;
    }

    public function getQrVisitsAttribute(): int
    {
        $visits = $this->statistics()
            ->where('action', 'scan-card')
            ->first();
        return $visits != null ? intval($visits->data) : 0;
    }

    public function getUrlAttribute(): string
    {
        return url("{$this->client->slug}/{$this->slug}");
    }

    public function getUrlNumberAttribute(): string
    {
        return url("{$this->client->slug}/{$this->slug_number}");
    }

    public function getVcardAttribute(): string
    {
        return url("storage/cards/card-{$this->slug}.vcf");
    }

    public function getNameAttribute(): string
    {
        return $this->field(GroupField::OTHERS, 'name') ?: '';
    }

    public function getEmailAttribute(): string
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
                    $isJson = $value['type'] == FieldType::GRADIENT;
                    $isMarkdown = $value['type'] == FieldType::TEXTAREA;

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

        $isMarkdown = $field->type == FieldType::TEXTAREA;
        $isJson = $field->type == FieldType::GRADIENT;

        if ($isJson) {
            return json_decode($field->value);
        } elseif ($isMarkdown && $withMarkdown) {
            return markdown($field->value);
        }

        return $field->value;
    }
}
