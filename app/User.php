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

    public function getRoleDescriptionAttribute()
    {
        $list = [
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_CLIENT => 'Cliente',
        ];
        return $list[$this->role];
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

    public function isClient()
    {
        return $this->role === self::ROLE_CLIENT;
    }

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function getCardsUsageAttribute()
    {
        $subscription = $this->subscriptions()->first();

        if ($subscription == null) {
            return "0/0";
        }

        $cards = $this->cards()->count();

        return "$cards/{$subscription->cards}";
    }

    public function getSubscriptionStatusAttribute()
    {
        $subscription = $this->subscriptions()->first();

        if ($subscription == null) {
            return "No tiene suscripción";
        }

        return $subscription->finish_at->format('d/m/Y h:ia');
    }

    public function scopeOnlyClients($query)
    {
        return $query->whereRole(self::ROLE_CLIENT);
    }

    public function scopeOnlyAdmins($query)
    {
        return $query->whereRole(self::ROLE_ADMIN);
    }
}
