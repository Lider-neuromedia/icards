<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    const ROLE_ADMIN = "admin"; // Administradores de neuromedia.
    const ROLE_CLIENT = "client"; // Clientes que han pagado suscripción.

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function cards()
    {
        return $this->hasMany(Card::class, 'client_id', 'id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'client_id', 'id');
    }

    public function getLogoAttribute()
    {
        $myself = $this->id;
        $field = CardField::query()
            ->whereHas('card', function ($q) use ($myself) {
                $q->whereHas('client', function ($q) use ($myself) {
                    $q->where('id', $myself);
                });
            })
            ->where('group', CardField::GROUP_OTHERS)
            ->where('key', 'logo')
            ->whereNotNull('value')
            ->first();

        return $field ? url("storage/cards/{$field->value}") : null;
    }
}
