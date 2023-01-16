<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PharIo\Manifest\Email;
use Symfony\Component\Mailer\Envelope;

class Employee extends Mailable
{
    use Queueable, SerializesModels;

    protected $mailType;
    protected $mailData;
    const MAIL_1 = '【DEV J PORTAL】Employee Registration Approval';
    const MAIL_2 = '【DEV J PORTAL】Account Activated';
    const MAIL_3 = '【DEV J PORTAL】Employee Registration Rejected';
    const MAIL_4 = '【DEV J PORTAL】Employee Update Request';
    const MAIL_5 = '【DEV J PORTAL】Approved Employee Update ';
    const MAIL_6 = '【DEV J PORTAL】Employee Update Rejected';
    const MAIL_7 = '【DEV J PORTAL】Employee Detail Updated';
    const MAIL_8 = '【DEV J PORTAL】Employee Linking to a Project Request';
    const MAIL_9 = '【DEV J PORTAL】Employee Linking to a Laptop Request';
    const MAIL_10 = '【DEV J PORTAL】Project Linked';
    const MAIL_11 = '【DEV J PORTAL】Laptop Linked';
    const MAIL_12 = '【DEV J PORTAL】Account Deactivation';
    const MAIL_13 = '【DEV J PORTAL】Surrender of Assets';
    const MAIL_14 = '【DEV J PORTAL】Account Reactivation';

    

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
