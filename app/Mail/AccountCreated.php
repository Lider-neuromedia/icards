<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $client;
    protected $credentials;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $client, array $credentials)
    {
        $this->client = $client;
        $this->credentials = $credentials;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.welcome')
            ->subject('iCard | Le damos la bienvenida')
            ->with([
                'name' => $this->client->name,
                'credentials' => $this->credentials,
            ]);
    }
}
