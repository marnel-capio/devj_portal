<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class updateContactDetailsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = env('APP_ENV') != 'production' ? "【" . strtoupper(env('APP_ENV')) . "】".'【DEV J PORTAL】 Update Your Employee Details' : '【DEV J PORTAL】 Update Your Employee Details';
        return $this
            ->subject($subject)
                ->view('mail.updateContactDetailsNotification', ['mailData' => $this->mailData]);
    }
}
