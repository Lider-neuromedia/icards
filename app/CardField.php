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
                ['key' => 'logo', 'label' => 'Logo', 'type' => 'image', 'general' => true, 'default' => null],
                ['key' => 'name', 'label' => 'Nombre', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'cargo', 'label' => 'Cargo', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'company', 'label' => 'Empresa', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'description', 'label' => 'Descripción', 'type' => 'textarea', 'general' => false, 'default' => ''],
            ],
        ],
        'action_contacts' => [
            'label' => 'Contacto Principal',
            'values' => [
                ['key' => 'phone', 'label' => 'Teléfono', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'email', 'label' => 'E-mail', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'whatsapp', 'label' => 'Whatsapp', 'type' => 'text', 'general' => false, 'default' => ''],
            ],
        ],
        'contact_list' => [
            'label' => 'Datos de Contacto',
            'values' => [
                ['key' => 'cellphone', 'label' => 'Celular', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'phone1', 'label' => 'Teléfono 1', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'phone2', 'label' => 'Teléfono 2', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'email', 'label' => 'E-mail', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'web', 'label' => 'Página Web', 'type' => 'text', 'general' => false, 'default' => ''],
            ],
        ],
        'social_list' => [
            'label' => 'Redes Sociales',
            'values' => [
                ['key' => 'facebook', 'label' => 'Facebook', 'type' => 'text', 'general' => true, 'default' => ''],
                ['key' => 'instagram', 'label' => 'Instagram', 'type' => 'text', 'general' => true, 'default' => ''],
                ['key' => 'linkedin', 'label' => 'LinkedIn', 'type' => 'text', 'general' => true, 'default' => ''],
                ['key' => 'twitter', 'label' => 'Twitter', 'type' => 'text', 'general' => true, 'default' => ''],
                ['key' => 'youtube', 'label' => 'YouTube', 'type' => 'text', 'general' => true, 'default' => ''],
            ],
        ],
        'theme' => [
            'label' => 'Tema Visual',
            'values' => [
                ['key' => 'main_color', 'label' => 'Color Principal', 'type' => 'color', 'general' => true, 'default' => '#ff0000'],
                ['key' => 'header_bg_color', 'label' => 'Cabecera Color Fondo', 'type' => 'color', 'general' => true, 'default' => '#ff0000'],
                ['key' => 'header_text_color', 'label' => 'Cabecera Color Texto', 'type' => 'color', 'general' => true, 'default' => '#ffffff'],
            ],
        ],
    ];
}
