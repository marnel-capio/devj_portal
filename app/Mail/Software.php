<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PharIo\Manifest\Email;
use Symfony\Component\Mailer\Envelope;

class Software extends Mailable
{
    use Queueable, SerializesModels;

    protected $mailType;
    protected $mailData;
    const MAIL_12 = '【DEV J PORTAL】Software approval';
    const MAIL_13 = '【DEV J PORTAL】Software request approved';
    const MAIL_14 = '【DEV J PORTAL】Software request rejected';
    const MAIL_15 = '【DEV J PORTAL】Software Details Update Approval Request';
    const MAIL_16 = '【DEV J PORTAL】Software update request approved';
    const MAIL_17 = '【DEV J PORTAL】Software update request rejected';
    const MAIL_18 = '【DEV J PORTAL】【DEV J PORTAL】Project Link to Software Request';

    /**
     * Create a new message instance.
     *
     * @return void 
     */
    public function __construct($mailData, $mailType)
    {
        $this->mailData = $mailData;
        $this->mailType = $mailType;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(constant("self::MAIL_{$this->mailType}"))
            ->view('mail.employee', ['mailData' => $this->mailData, 'mailType' => $this->mailType]);
    }
}
