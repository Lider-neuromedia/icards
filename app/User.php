<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Enums\GroupField;
use App\Mail\AdminRenewSubsNotified;
use App\Mail\RenewSubscriptionNotified;

class User extends Authenticatable
{
    use Notifiable;

    // TODO: Mover a EnumClass
    public const ROLE_ADMIN = "admin"; // Administradores de neuromedia.
    public const ROLE_CLIENT = "client"; // Clientes que han pagado suscripción.

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'slug',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'seller_name',
    ];

    public function sellers()
    {
        return $this->belongsToMany(Seller::class, 'client_seller', 'client_id', 'seller_id');
    }

    public function cards()
    {
        return $this->hasMany(Card::class, 'client_id', 'id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'client_id', 'id');
    }

    /**
     * Cuentas a las que esta cuenta puede acceder.
     */
    public function allowedAccounts()
    {
        return $this->belongsToMany(User::class, 'allowed_accounts', 'client_id', 'allowed_account_id');
    }

    /**
     * Cuentas que pueden acceder a esta cuenta.
     */
    public function allowedClients()
    {
        return $this->belongsToMany(User::class, 'allowed_accounts', 'allowed_account_id', 'client_id');
    }

    public function getSellerNameAttribute()
    {
        $seller = $this->sellers()->first();
        return $seller == null ? 'Sin Vendedor' : $seller->name;
    }

    public function getRoleDescriptionAttribute()
    {
        return self::roles()[$this->role];
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
            ->where('group', GroupField::OTHERS)
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

    public function isCardsLimitReached()
    {
        $subscription = $this->subscriptions()->first();

        if ($subscription == null) {
            return true;
        }

        return $this->cards()->count() >= $subscription->cards;
    }

    /**
     * El usuario tiene asociada o es la cuenta pasada por parámetro.
     * @param User|Authenticatable $account
     * @return bool
     */
    public function hasAllowedAccount(User $account): bool
    {
        if ($this->isAdmin()) {
            return true;
        } elseif ($this->id == $account->id) {
            return true;
        }
        $hasAccount = $this->allowedAccounts()
            ->where('allowed_account_id', $account->id)
            ->exists();
        return $hasAccount;
    }

    /**
     * El usuario tiene asociada o es dueño de la tarjeta pasada por parámetro.
     *
     * @param Card $card
     * @return bool
     */
    public function hasAllowedCard(Card $card): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        $hasCard = $this->hasAllowedAccount($card->client()->first());
        return $hasCard;
    }

    /**
     * @param User|Authenticatable $account
     * @return bool
     */
    public function hasNotAllowedAccount(User $account): bool
    {
        return !$this->hasAllowedAccount($account);
    }

    /**
     * @param Card $card
     * @return bool
     */
    public function hasNotAllowedCard(Card $card): bool
    {
        return !$this->hasAllowedCard($card);
    }

    public function getCardsUsageAttribute()
    {
        $subscription = $this->subscriptions()->first();
        $cards = $this->cards()->count();
        return $subscription != null ? "$cards/{$subscription->cards}" : "0/0";
    }

    /**
     * Obtener suscripción disponible de cliente.
     *
     * @return Subscription $subscription
     */
    public function subscription()
    {
        $now = now()->format('Y-m-d H:i:s');
        return $this->subscriptions()->where('finish_at', '>', $now)->first();
    }

    /**
     * Obtener la cantidad de días que le quedan de suscripción al usuario.
     */
    public function getSubscriptionDaysLeft()
    {
        $sub = $this->subscriptions()->first();
        return $sub != null ? now()->diffInDays($sub->finish_at, false) : 0;
    }

    public function getSubscriptionStatusAttribute()
    {
        $subscription = $this->subscriptions()->first();
        return $subscription != null ? $subscription->finish_at->format('d/m/Y h:ia') : "No tiene suscripción";
    }

    public function getIsSubscriptionActiveAttribute()
    {
        $left = $this->getSubscriptionDaysLeft();
        return $left == null || $left <= 0 ? false : true;
    }

    public function getLastNotificationInDaysAttribute()
    {
        $sub = $this->subscriptions()->first();

        if ($sub == null) {
            return 365;
        } elseif ($sub->notified_at == null) {
            return 365;
        }

        return now()->diffInDays($sub->notified_at);
    }

    public function scopeOnlyClients($query)
    {
        return $query->whereRole(self::ROLE_CLIENT);
    }

    public function scopeOnlyAdmins($query)
    {
        return $query->whereRole(self::ROLE_ADMIN);
    }

    /**
     * TODO: Mover a EnumClass
     */
    public static function roles()
    {
        return [
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_CLIENT => 'Cliente',
        ];
    }

    /**
     * Notificar usuarios por correo cuando su suscripción está por vencer o se ha vencido.
     * 1. Clientes con suscripción que venza dentro de 20 días o menos.
     * 2. Clientes que no hayan sido notificados.
     * 3. Clientes que hayan sido notificados hace 7 dias o mas.
     */
    public static function notifyClientsWithExpireSoonSuscriptions()
    {
        $clients = User::query()
            ->whereRole(User::ROLE_CLIENT)
            ->whereHas('subscriptions', function ($q) {
                $q->whereDate('finish_at', '<=', now()->addDays(20)) // 1.
                    ->where(function ($q) {
                        $q->whereNull('notified_at') // 2.
                            ->orWhere('notified_at', '<=', now()->subDays(7)); // 3.
                    });
            })
            ->with('subscriptions')
            ->get();

        $count = $clients->count();
        $now = now()->format('Y-m-d H:i:s');
        Log::info("Notificar clientes ($count) con suscripción a vencer: $now.");

        foreach ($clients as $client) {
            Log::info("Notificar cliente: {$client->id}.");
            Mail::to($client)->send(new RenewSubscriptionNotified($client));

            $client->subscriptions()
                ->first()
                ->update(['notified_at' => $now]);
        }

        if ($count > 0) {
            // Enviar Notificación a administradores.
            Log::info("Notificar administradores");
            $admins = User::whereRole(User::ROLE_ADMIN)->get();
            Mail::to($admins)->send(new AdminRenewSubsNotified($clients));
        }
    }

    public static function testEmails()
    {
        $client = User::onlyClients()->inRandomOrder()->firstOrFail();
        $card = $client->cards()->inRandomOrder()->firstOrFail();
        $credentials = ['email' => $client->email, 'password' => Str::random(12)];

        Mail::to($client)->send(new \App\Mail\AccountCreated($client, $credentials));
        Mail::to($client)->send(new \App\Mail\CardCreated($card));
        Mail::to($client)->send(new RenewSubscriptionNotified($client));
    }
}
