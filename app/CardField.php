<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CardField extends Model
{
    const GROUP_OTHERS = 'others';
    const GROUP_ACTION_CONTACTS = 'action_contacts';
    const GROUP_CONTACT_LIST = 'contact_list';
    const GROUP_SOCIAL_LIST = 'social_list';
    const GROUP_THEME = 'theme';

    protected $fillable = [
        'group',
        'key',
        'value',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function getUrlAttribute()
    {
        return url("storage/cards/{$this->value}");
    }

    public function getTypeAttribute()
    {
        foreach (self::TEMPLATE_FIELDS as $key => $group) {
            if ($this->group === $key) {
                foreach ($group['values'] as $value) {
                    if ($this->key === $value['key']) {
                        return $value['type'];
                    }
                }
            }
        }
        return 'text';
    }

    const TEMPLATE_FIELDS = [
        'others' => [
            'label' => 'Datos de Tarjeta',
            'values' => [
                ['key' => 'logo', 'label' => 'Logo', 'type' => 'image'],
                ['key' => 'name', 'label' => 'Nombre', 'type' => 'text'],
                ['key' => 'cargo', 'label' => 'Cargo', 'type' => 'text'],
                ['key' => 'company', 'label' => 'Empresa', 'type' => 'text'],
                ['key' => 'description', 'label' => 'Descripción', 'type' => 'textarea'],
            ],
        ],
        'action_contacts' => [
            'label' => 'Contacto Principal',
            'values' => [
                ['key' => 'phone', 'label' => 'Teléfono', 'type' => 'text'],
                ['key' => 'email', 'label' => 'E-mail', 'type' => 'text'],
                ['key' => 'whatsapp', 'label' => 'Whatsapp', 'type' => 'text'],
            ],
        ],
        'contact_list' => [
            'label' => 'Datos de Contacto',
            'values' => [
                ['key' => 'cellphone', 'label' => 'Celular', 'type' => 'text'],
                ['key' => 'phone', 'label' => 'Teléfono 1', 'type' => 'text'],
                ['key' => 'phone', 'label' => 'Teléfono 2', 'type' => 'text'],
                ['key' => 'email', 'label' => 'E-mail', 'type' => 'text'],
                ['key' => 'web', 'label' => 'Página Web', 'type' => 'text'],
            ],
        ],
        'social_list' => [
            'label' => 'Redes Sociales',
            'values' => [
                ['key' => 'facebook', 'label' => 'Facebook', 'type' => 'text'],
                ['key' => 'instagram', 'label' => 'Instagram', 'type' => 'text'],
                ['key' => 'linkedin', 'label' => 'LinkedIn', 'type' => 'text'],
                ['key' => 'twitter', 'label' => 'Twitter', 'type' => 'text'],
                ['key' => 'youtube', 'label' => 'YouTube', 'type' => 'text'],
            ],
        ],
        'theme' => [
            'label' => 'Tema Visual',
            'values' => [
                ['key' => 'main_color', 'label' => 'Color Principal', 'type' => 'color'],
            ],
        ],
    ];
}
