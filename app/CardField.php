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

    public static function hasGroupWithGeneralFields($group)
    {
        foreach (self::TEMPLATE_FIELDS as $key => $fields_group) {
            if ($key == $group) {
                foreach ($fields_group['values'] as $value) {
                    if ($value['general'] === true) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public static function hasGroupWithSpecificFields($group)
    {
        foreach (self::TEMPLATE_FIELDS as $key => $fields_group) {
            if ($key == $group) {
                foreach ($fields_group['values'] as $value) {
                    if ($value['general'] === false) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    const TEMPLATE_FIELDS = [
        'others' => [
            'label' => 'Datos de Tarjeta',
            'values' => [
                ['key' => 'logo', 'label' => 'Logo', 'type' => 'image', 'general' => true],
                ['key' => 'name', 'label' => 'Nombre', 'type' => 'text', 'general' => false],
                ['key' => 'cargo', 'label' => 'Cargo', 'type' => 'text', 'general' => false],
                ['key' => 'company', 'label' => 'Empresa', 'type' => 'text', 'general' => false],
                ['key' => 'description', 'label' => 'Descripción', 'type' => 'textarea', 'general' => false],
            ],
        ],
        'action_contacts' => [
            'label' => 'Contacto Principal',
            'values' => [
                ['key' => 'phone', 'label' => 'Teléfono', 'type' => 'text', 'general' => false],
                ['key' => 'email', 'label' => 'E-mail', 'type' => 'text', 'general' => false],
                ['key' => 'whatsapp', 'label' => 'Whatsapp', 'type' => 'text', 'general' => false],
            ],
        ],
        'contact_list' => [
            'label' => 'Datos de Contacto',
            'values' => [
                ['key' => 'cellphone', 'label' => 'Celular', 'type' => 'text', 'general' => false],
                ['key' => 'phone1', 'label' => 'Teléfono 1', 'type' => 'text', 'general' => false],
                ['key' => 'phone2', 'label' => 'Teléfono 2', 'type' => 'text', 'general' => false],
                ['key' => 'email', 'label' => 'E-mail', 'type' => 'text', 'general' => false],
                ['key' => 'web', 'label' => 'Página Web', 'type' => 'text', 'general' => false],
            ],
        ],
        'social_list' => [
            'label' => 'Redes Sociales',
            'values' => [
                ['key' => 'facebook', 'label' => 'Facebook', 'type' => 'text', 'general' => false],
                ['key' => 'instagram', 'label' => 'Instagram', 'type' => 'text', 'general' => false],
                ['key' => 'linkedin', 'label' => 'LinkedIn', 'type' => 'text', 'general' => false],
                ['key' => 'twitter', 'label' => 'Twitter', 'type' => 'text', 'general' => false],
                ['key' => 'youtube', 'label' => 'YouTube', 'type' => 'text', 'general' => false],
            ],
        ],
        'theme' => [
            'label' => 'Tema Visual',
            'values' => [
                ['key' => 'main_color', 'label' => 'Color Principal', 'type' => 'color', 'general' => true],
            ],
        ],
    ];
}
