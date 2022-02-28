<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CardStatistic extends Model
{
    protected $fillable = [
        'action',
        'data',
    ];

    protected $appends = [
        'title',
        'name',
        'url',
    ];

    public static function analyticsEvents()
    {
        return [
            (Object) ['key' => 'contact-by-call', 'description' => 'Contactos por Teléfono', 'title' => 'Teléfono'],
            (Object) ['key' => 'contact-by-email', 'description' => 'Contactos por Correo', 'title' => 'Correo'],
            (Object) ['key' => 'contact-by-whatsapp', 'description' => 'Contactos por Whatsapp', 'title' => 'Whatsapp'],
            (Object) ['key' => 'save-contact', 'description' => 'Veces Guardado', 'title' => 'Guardar'],
            (Object) ['key' => 'share-contact', 'description' => 'Veces Compartido', 'title' => 'Compartido'],
            (Object) ['key' => 'save-image', 'description' => 'Veces Imagen Guardada', 'title' => 'Imagen'],
            (Object) ['key' => 'visit-web', 'description' => 'Página Visitada', 'title' => 'Web'],
            (Object) ['key' => 'visit-facebook', 'description' => 'Facebook Visitado', 'title' => 'Facebook'],
            (Object) ['key' => 'visit-instagram', 'description' => 'Instagram Visitado', 'title' => 'Instagram'],
            (Object) ['key' => 'visit-linkedin', 'description' => 'Linkedin Visitado', 'title' => 'LinkedIn'],
            (Object) ['key' => 'visit-twitter', 'description' => 'Twitter Visitado', 'title' => 'Twitter'],
            (Object) ['key' => 'visit-youtube', 'description' => 'YouTube Visitado', 'title' => 'YouTube'],
        ];
    }

    public static function allAnalyticsEvents()
    {
        return [
            'scan-card' => 'Total Escaneos',
            'visit-card' => 'Total Visitas',
            'contact-by-call' => 'Contactos por Teléfono',
            'contact-by-email' => 'Contactos por Correo',
            'contact-by-whatsapp' => 'Contactos por Whatsapp',
            'save-contact' => 'Veces Guardado',
            'share-contact' => 'Veces Compartido',
            'save-image' => 'Veces Imagen Guardada',
            'visit-web' => 'Página Visitada',
            'visit-facebook' => 'Facebook Visitado',
            'visit-instagram' => 'Instagram Visitado',
            'visit-linkedin' => 'Linkedin Visitado',
            'visit-twitter' => 'Twitter Visitado',
            'visit-youtube' => 'YouTube Visitado',
        ];
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function getUrlAttribute()
    {
        return $this->card->url;
    }

    public function getNameAttribute()
    {
        return $this->card->name;
    }

    public function getTitleAttribute()
    {
        return self::allAnalyticsEvents()[$this->action];
    }
}
