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
                ['key' => 'profile', 'label' => 'Foto de perfil', 'type' => 'image', 'general' => false, 'default' => null, 'help' => '(jpeg, jpg, png) Tamaño máximo: 900 kilobytes'],
                ['key' => 'logo', 'label' => 'Logo', 'type' => 'image', 'general' => true, 'default' => null, 'help' => '(jpeg, jpg, png) Tamaño máximo: 900 kilobytes'],
                ['key' => 'logo_bg', 'label' => 'Fondo de logo', 'type' => 'color', 'general' => true, 'default' => '#ffffff'],
                ['key' => 'name', 'label' => 'Nombre', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'cargo', 'label' => 'Cargo', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'company', 'label' => 'Empresa', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'description', 'label' => 'Descripción', 'type' => 'textarea', 'general' => false, 'default' => ''],
            ],
        ],
        'action_contacts' => [
            'label' => 'Contacto Principal',
            'values' => [
                ['key' => 'phone', 'label' => 'Teléfono', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'Código País + Número. Ej: +573108154510.'],
                ['key' => 'email', 'label' => 'E-mail', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'ejemplo: micontacto@mail.com'],
                ['key' => 'whatsapp', 'label' => 'Whatsapp', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'Ingresar código del país y número. Ejemplo: +573108154510.'],
                ['key' => 'whatsapp_message', 'label' => 'Mensaje de Whatsapp', 'type' => 'text', 'general' => false, 'default' => 'Hola, en que te puedo ayudar'],
            ],
        ],
        'contact_list' => [
            'label' => 'Datos de Contacto',
            'values' => [
                ['key' => 'cellphone', 'label' => 'Celular', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'Código País + Número. Ej: +573108154510.'],
                ['key' => 'phone1', 'label' => 'Teléfono 1', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'Indicativo + Número. Ej: 6011234567'],
                ['key' => 'phone2', 'label' => 'Teléfono 2', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'Indicativo + Número. Ej: 6011234567'],
                ['key' => 'email', 'label' => 'E-mail', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'ejemplo: micontacto@mail.com'],
                ['key' => 'web', 'label' => 'Página Web', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'ejemplo: https://mipagina.com'],
            ],
        ],
        'social_list' => [
            'label' => 'Redes Sociales',
            'values' => [
                ['key' => 'facebook', 'label' => 'Facebook', 'type' => 'text', 'general' => true, 'default' => '', 'help' => 'ejemplo: https://www.facebook.com/mi-empresa/?fref=ts'],
                ['key' => 'instagram', 'label' => 'Instagram', 'type' => 'text', 'general' => true, 'default' => '', 'help' => 'ejemplo: https://www.instagram.com/mi-empresa/'],
                ['key' => 'linkedin', 'label' => 'LinkedIn', 'type' => 'text', 'general' => true, 'default' => '', 'help' => 'ejemplo: https://www.linkedin.com/company/mi-empresa/'],
                ['key' => 'twitter', 'label' => 'Twitter', 'type' => 'text', 'general' => true, 'default' => '', 'help' => 'ejemplo: https://twitter.com/mi-empresa'],
                ['key' => 'youtube', 'label' => 'YouTube', 'type' => 'text', 'general' => true, 'default' => '', 'help' => 'ejemplo: https://www.youtube.com/channel/Mi-Empresa'],
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
