<?php

namespace App\Mail;

use App\Card;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CardCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $card;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Card $card)
    {
        $this->card = $card;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.welcome-card')
            ->subject('iCard | Su tarjeta de presentación está disponible')
            ->with([
                'name' => $this->card->name,
                'url' => $this->card->url,
            ]);
    }
}
