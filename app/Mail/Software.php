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
    const MAIL_1 = '【DEV J PORTAL】Software Approval Request';
    const MAIL_2 = '【DEV J PORTAL】Software Request Approved';
    const MAIL_3 = '【DEV J PORTAL】Software Request Rejected';
    const MAIL_4 = '【DEV J PORTAL】Software Details Update Approval Request';
    const MAIL_5 = '【DEV J PORTAL】Software Update Request Approved';
    const MAIL_6 = '【DEV J PORTAL】Software Update Request Rejected';
    const MAIL_7 = '【DEV J PORTAL】Project Link to Software Request';

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
