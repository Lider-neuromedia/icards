<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\User;

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
            ->subject('Bienvenido a la era digital 🤖')
            ->with([
                'name' => $this->client->name,
                'credentials' => $this->credentials,
            ]);
    }
}
