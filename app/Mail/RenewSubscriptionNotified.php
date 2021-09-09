<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RenewSubscriptionNotified extends Mailable
{
    use Queueable, SerializesModels;

    protected $client;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $client)
    {
        $this->client = $client;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.renew')
            ->subject('Notificación de vencimiento de suscripción de iCard')
            ->with([
                'days' => $this->client->getSubscriptionDaysLeft(),
            ]);
    }
}
