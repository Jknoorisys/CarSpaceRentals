<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class dealerforgetpass extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $token)
    {
        $this->name = $name;
        $this->token = $token;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $dealer['name'] = $this->name;
        $dealer['token'] = $this->token;

        return $this->from("aaisha.noorisys@gmail.com", "Car Space Rental")
        ->subject('Dealer Password Reset Link')
        ->view('Dealer_Mail.forget-pass', ['dealer' => $dealer]);
    }
}
