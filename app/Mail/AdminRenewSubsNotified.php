<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminRenewSubsNotified extends Mailable
{
    use Queueable, SerializesModels;

    public $clients;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($clients)
    {
        $this->clients = $clients;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.admin-renew')
            ->subject('iCard | Notificación de vencimiento de suscripciones');
    }
}
