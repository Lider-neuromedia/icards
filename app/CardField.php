<?php

namespace App;

use App\Enums\FieldType;
use App\Enums\GroupField;
use App\Models\Field;
use Illuminate\Database\Eloquent\Model;

class CardField extends Model
{
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

    // TODO: Usar DTO y mover a un servicio aparte.
    public const TEMPLATES = [
        [
            'id' => 'default',
            'name' => 'Plantilla por Defecto',
            'stylesPath' => 'css/template-default.css',
            'templatePath' => 'ecard.ecard',
        ],
        [
            'id' => 'bigphoto',
            'name' => 'Plantilla Gran Foto',
            'stylesPath' => 'css/template-bigphoto.css',
            'templatePath' => 'ecard.ecard-bigphoto',
        ],
    ];

    // TODO: Usar DTO y mover a un servicio aparte.
    public const TEMPLATE_FIELDS = [
        GroupField::OTHERS => [
            'label' => 'Datos de Tarjeta',
            'values' => [
                ['key' => 'default_lang', 'label' => 'Idioma de tarjetas', 'type' => FieldType::SELECT, 'general' => Field::GENERAL, 'default' => 'es', 'options' => [
                    ['id' => 'es', 'name' => 'Español'],
                    ['id' => 'en', 'name' => 'Inglés'],
                ], 'example' => 'es'],
                ['key' => 'profile', 'label' => 'Foto de perfil', 'type' => FieldType::IMAGE, 'general' => Field::SPECIFIC, 'default' => null, 'help' => '(jpeg, jpg, png) Tamaño máximo: 250kb', 'max' => 250, 'example' => 'foto-nombre.jpg'],
                ['key' => 'logo', 'label' => 'Logo Cabecera', 'type' => FieldType::IMAGE, 'general' => Field::GENERAL, 'default' => null, 'help' => '(jpeg, jpg, png) Tamaño máximo: 900kb', 'max' => 900, 'example' => 'logo.jpg'],
                ['key' => 'logo_card', 'label' => 'Logo Tarjeta', 'type' => FieldType::IMAGE, 'general' => Field::GENERAL, 'default' => null, 'help' => 'Si no se pone, se usará el logo de la cabecera. (jpeg, jpg, png) Tamaño máximo: 900kb', 'max' => 900, 'example' => 'logo-tarjeta.jpg'],
                ['key' => 'has_logo_bg', 'label' => 'Aplicar "Fondo de logo"', 'type' => FieldType::BOOLEAN, 'general' => Field::GENERAL, 'default' => '0', 'help' => 'Si no se marca la casilla, el logo queda sin color de fondo.', 'watch' => true, 'example' => 1],
                ['key' => 'logo_bg', 'label' => 'Fondo de logo', 'type' => FieldType::COLOR, 'general' => Field::GENERAL, 'default' => '#ffffff', 'visible_when' => 'has_logo_bg:1', 'example' => '#ff00ff'],
                ['key' => 'name', 'label' => 'Nombre', 'type' => FieldType::TEXT, 'general' => Field::SPECIFIC, 'default' => '', 'example' => 'Juan Perez'],
                ['key' => 'cargo', 'label' => 'Cargo', 'type' => FieldType::TEXT, 'general' => Field::SPECIFIC, 'default' => '', 'example' => 'Vendedor'],
                ['key' => 'company', 'label' => 'Empresa', 'type' => FieldType::TEXT, 'general' => Field::GENERAL, 'default' => '', 'example' => 'Nombre Empresa'],
                ['key' => 'description', 'label' => 'Descripción', 'type' => FieldType::TEXTAREA, 'general' => Field::SPECIFIC, 'default' => '', 'example' => 'Texto extenso de ejemplo para llenar descripción de tarjeta'],
                ['key' => 'use_card_number', 'label' => 'Usar Número en Tarjeta', 'type' => FieldType::BOOLEAN, 'general' => Field::GENERAL, 'default' => '0', 'help' => 'Usando número https://icard.neuromedia.com.co/empresa/juan-hernesto-perez. Sin usar número https://icard.neuromedia.com.co/empresa/16', 'example' => 0],
            ],
        ],
        GroupField::ACTION_CONTACTS => [
            'label' => 'Contacto Principal',
            'values' => [
                ['key' => 'phone', 'label' => 'Teléfono', 'type' => FieldType::TEXT, 'general' => Field::SPECIFIC, 'default' => '', 'help' => 'Código País + Número. Ej: +573108154510.', 'example' => '+573108004011'],
                ['key' => 'email', 'label' => 'E-mail', 'type' => FieldType::TEXT, 'general' => Field::SPECIFIC, 'default' => '', 'help' => 'Ejemplo: micontacto@mail.com', 'example' => 'micontacto@mail.com'],
                ['key' => 'whatsapp', 'label' => 'Whatsapp', 'type' => FieldType::TEXT, 'general' => Field::SPECIFIC, 'default' => '', 'help' => 'Ingresar código del país y número. Ejemplo: +573108154510.', 'example' => '+573108004012'],
                ['key' => 'whatsapp_message', 'label' => 'Mensaje de Whatsapp', 'type' => FieldType::TEXT, 'general' => Field::SPECIFIC, 'default' => 'Hola, en que te puedo ayudar', 'example' => 'Hola, en que te puedo ayudar'],
            ],
        ],
        GroupField::CONTACT_LIST => [
            'label' => 'Datos de Contacto',
            'values' => [
                ['key' => 'cellphone', 'label' => 'Celular', 'type' => FieldType::TEXT, 'general' => Field::SPECIFIC, 'default' => '', 'help' => 'Código País + Número. Ej: +573108154510.', 'example' => '+573108104010'],
                ['key' => 'phone1', 'label' => 'Teléfono 1', 'type' => FieldType::TEXT, 'general' => Field::SPECIFIC, 'default' => '', 'help' => 'Indicativo + Número. Ej: 6011234567', 'example' => '6011204061'],
                ['key' => 'phone2', 'label' => 'Teléfono 2', 'type' => FieldType::TEXT, 'general' => Field::SPECIFIC, 'default' => '', 'help' => 'Indicativo + Número. Ej: 6011234567', 'example' => '6011202082'],
                ['key' => 'email', 'label' => 'E-mail', 'type' => FieldType::TEXT, 'general' => Field::SPECIFIC, 'default' => '', 'help' => 'Ejemplo: micontacto@mail.com', 'example' => 'micontacto@mail.com'],
                ['key' => 'web', 'label' => 'Página Web', 'type' => FieldType::TEXT, 'general' => Field::GENERAL, 'default' => '', 'help' => 'Ejemplo: https://mipagina.com', 'example' => 'https://mipagina.com'],
                ['key' => 'address', 'label' => 'Dirección', 'type' => FieldType::TEXTAREA, 'general' => Field::GENERAL, 'default' => '', 'help' => 'Ubicación física de la empresa', 'example' => 'Calle 13 # 43b - 33'],
            ],
        ],
        GroupField::SOCIAL_LIST => [
            'label' => 'Redes Sociales',
            'values' => [
                ['key' => 'facebook', 'label' => 'Facebook', 'type' => FieldType::TEXT, 'general' => Field::GENERAL, 'default' => '', 'help' => 'Ejemplo: https://www.facebook.com/mi-empresa/?fref=ts', 'example' => 'https://www.facebook.com/mi-empresa/?fref=ts'],
                ['key' => 'instagram', 'label' => 'Instagram', 'type' => FieldType::TEXT, 'general' => Field::GENERAL, 'default' => '', 'help' => 'Ejemplo: https://www.instagram.com/mi-empresa/', 'example' => 'https://www.instagram.com/mi-empresa/'],
                ['key' => 'linkedin', 'label' => 'LinkedIn', 'type' => FieldType::TEXT, 'general' => Field::GENERAL, 'default' => '', 'help' => 'Ejemplo: https://www.linkedin.com/company/mi-empresa/', 'example' => 'https://www.linkedin.com/company/mi-empresa/'],
                ['key' => 'twitter', 'label' => 'Twitter', 'type' => FieldType::TEXT, 'general' => Field::GENERAL, 'default' => '', 'help' => 'Ejemplo: https://twitter.com/mi-empresa', 'example' => 'https://twitter.com/mi-empresa'],
                ['key' => 'youtube', 'label' => 'YouTube', 'type' => FieldType::TEXT, 'general' => Field::GENERAL, 'default' => '', 'help' => 'Ejemplo: https://www.youtube.com/channel/Mi-Empresa', 'example' => 'https://www.youtube.com/channel/Mi-Empresa'],
            ],
        ],
        GroupField::THEME => [
            'label' => 'Tema Visual',
            'values' => [
                ['key' => 'template', 'label' => 'Plantilla', 'type' => FieldType::SELECT, 'general' => Field::GENERAL, 'default' => 'default', 'options' => self::TEMPLATES, 'example' => null],
                ['key' => 'main_color', 'label' => 'Color Principal', 'type' => FieldType::COLOR, 'general' => Field::GENERAL, 'default' => '#ff0000', 'example' => '#ff00ff'],
                ['key' => 'header_text_color', 'label' => 'Color de Texto de Cabecera', 'type' => FieldType::COLOR, 'general' => Field::GENERAL, 'default' => '#ffffff', 'example' => '#ffffff'],
                ['key' => 'header_bg_type', 'label' => 'Tipo de Cabecera', 'type' => FieldType::SELECT, 'general' => Field::GENERAL, 'default' => 'header_bg_color', 'options' => [
                    ['id' => 'header_bg_color', 'name' => 'Color plano de fondo'],
                    ['id' => 'header_bg_gradient', 'name' => 'Gradiente de fondo'],
                    ['id' => 'header_bg_image', 'name' => 'Imagen de fondo'],
                ], 'watch' => true, 'example' => 'header_bg_color'],
                ['key' => 'header_bg_color', 'label' => 'Fondo de Cabecera (Color)', 'type' => FieldType::COLOR, 'general' => Field::GENERAL, 'default' => '#ff0000', 'visible_when' => 'header_bg_type:header_bg_color', 'example' => '#ff0000'],
                ['key' => 'header_bg_gradient', 'label' => 'Fondo de Cabecera (Gradiente)', 'type' => FieldType::GRADIENT, 'general' => Field::GENERAL, 'default' => '["#ff0000","#00ff00", "horizontal"]', 'visible_when' => 'header_bg_type:header_bg_gradient', 'example' => '["#ff0000","#00ff00","horizontal"]'],
                ['key' => 'header_bg_image', 'label' => 'Fondo de Cabecera (Imagen)', 'type' => FieldType::IMAGE, 'general' => Field::GENERAL, 'default' => null, 'help' => '(jpeg, jpg, png) Tamaño máximo: 900kb', 'max' => 900, 'visible_when' => 'header_bg_type:header_bg_image', 'example' => "fondo.jpg"],
            ],
        ],
    ];
}
