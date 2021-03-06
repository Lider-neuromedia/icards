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
                ['key' => 'profile', 'label' => 'Foto de perfil', 'type' => 'image', 'general' => false, 'default' => null, 'help' => '(jpeg, jpg, png) Tama??o m??ximo: 250 kilobytes', 'max' => 250],
                ['key' => 'logo', 'label' => 'Logo', 'type' => 'image', 'general' => true, 'default' => null, 'help' => '(jpeg, jpg, png) Tama??o m??ximo: 900 kilobytes', 'max' => 900],
                ['key' => 'has_logo_bg', 'label' => 'Aplicar "Fondo de logo"', 'type' => 'boolean', 'general' => true, 'default' => '0', 'help' => 'Si no se marca la casilla, el logo queda sin color de fondo.', 'watch' => true],
                ['key' => 'logo_bg', 'label' => 'Fondo de logo', 'type' => 'color', 'general' => true, 'default' => '#ffffff', 'visible_when' => 'has_logo_bg:1'],
                ['key' => 'name', 'label' => 'Nombre', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'cargo', 'label' => 'Cargo', 'type' => 'text', 'general' => false, 'default' => ''],
                ['key' => 'company', 'label' => 'Empresa', 'type' => 'text', 'general' => true, 'default' => ''],
                ['key' => 'description', 'label' => 'Descripci??n', 'type' => 'textarea', 'general' => false, 'default' => ''],
            ],
        ],
        'action_contacts' => [
            'label' => 'Contacto Principal',
            'values' => [
                ['key' => 'phone', 'label' => 'Tel??fono', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'C??digo Pa??s + N??mero. Ej: +573108154510.'],
                ['key' => 'email', 'label' => 'E-mail', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'Ejemplo: micontacto@mail.com'],
                ['key' => 'whatsapp', 'label' => 'Whatsapp', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'Ingresar c??digo del pa??s y n??mero. Ejemplo: +573108154510.'],
                ['key' => 'whatsapp_message', 'label' => 'Mensaje de Whatsapp', 'type' => 'text', 'general' => false, 'default' => 'Hola, en que te puedo ayudar'],
            ],
        ],
        'contact_list' => [
            'label' => 'Datos de Contacto',
            'values' => [
                ['key' => 'cellphone', 'label' => 'Celular', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'C??digo Pa??s + N??mero. Ej: +573108154510.'],
                ['key' => 'phone1', 'label' => 'Tel??fono 1', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'Indicativo + N??mero. Ej: 6011234567'],
                ['key' => 'phone2', 'label' => 'Tel??fono 2', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'Indicativo + N??mero. Ej: 6011234567'],
                ['key' => 'email', 'label' => 'E-mail', 'type' => 'text', 'general' => false, 'default' => '', 'help' => 'Ejemplo: micontacto@mail.com'],
                ['key' => 'web', 'label' => 'P??gina Web', 'type' => 'text', 'general' => true, 'default' => '', 'help' => 'Ejemplo: https://mipagina.com'],
                ['key' => 'address', 'label' => 'Direcci??n', 'type' => 'text', 'general' => true, 'default' => '', 'help' => 'Ubicaci??n f??sica de la empresa'],
            ],
        ],
        'social_list' => [
            'label' => 'Redes Sociales',
            'values' => [
                ['key' => 'facebook', 'label' => 'Facebook', 'type' => 'text', 'general' => true, 'default' => '', 'help' => 'Ejemplo: https://www.facebook.com/mi-empresa/?fref=ts'],
                ['key' => 'instagram', 'label' => 'Instagram', 'type' => 'text', 'general' => true, 'default' => '', 'help' => 'Ejemplo: https://www.instagram.com/mi-empresa/'],
                ['key' => 'linkedin', 'label' => 'LinkedIn', 'type' => 'text', 'general' => true, 'default' => '', 'help' => 'Ejemplo: https://www.linkedin.com/company/mi-empresa/'],
                ['key' => 'twitter', 'label' => 'Twitter', 'type' => 'text', 'general' => true, 'default' => '', 'help' => 'Ejemplo: https://twitter.com/mi-empresa'],
                ['key' => 'youtube', 'label' => 'YouTube', 'type' => 'text', 'general' => true, 'default' => '', 'help' => 'Ejemplo: https://www.youtube.com/channel/Mi-Empresa'],
            ],
        ],
        'theme' => [
            'label' => 'Tema Visual',
            'values' => [
                ['key' => 'main_color', 'label' => 'Color Principal', 'type' => 'color', 'general' => true, 'default' => '#ff0000'],
                ['key' => 'header_text_color', 'label' => 'Color de Texto de Cabecera', 'type' => 'color', 'general' => true, 'default' => '#ffffff'],
                ['key' => 'header_bg_type', 'label' => 'Tipo de Cabecera', 'type' => 'select', 'general' => true, 'default' => 'header_bg_color', 'options' => [
                    ['id' => 'header_bg_color', 'name' => 'Color plano de fondo'],
                    ['id' => 'header_bg_gradient', 'name' => 'Gradiente de fondo'],
                    ['id' => 'header_bg_image', 'name' => 'Imagen de fondo'],
                ], 'watch' => true],
                ['key' => 'header_bg_color', 'label' => 'Fondo de Cabecera (Color)', 'type' => 'color', 'general' => true, 'default' => '#ff0000', 'visible_when' => 'header_bg_type:header_bg_color'],
                ['key' => 'header_bg_gradient', 'label' => 'Fondo de Cabecera (Gradiente)', 'type' => 'gradient', 'general' => true, 'default' => '["#ff0000","#00ff00", "horizontal"]', 'visible_when' => 'header_bg_type:header_bg_gradient'],
                ['key' => 'header_bg_image', 'label' => 'Fondo de Cabecera (Imagen)', 'type' => 'image', 'general' => true, 'default' => null, 'help' => '(jpeg, jpg, png) Tama??o m??ximo: 900 kilobytes', 'max' => 900, 'visible_when' => 'header_bg_type:header_bg_image'],
            ],
        ],
    ];
}
