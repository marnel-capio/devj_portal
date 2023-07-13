<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Project extends Mailable
{
    use Queueable, SerializesModels;

    protected $mailType;
    protected $mailData;
    const MAIL_1 = '【DEV J PORTAL】 Project Linkage';
    const MAIL_2 = '【DEV J PORTAL】 New Project Linkage';
    const MAIL_3 = '【DEV J PORTAL】 Project Linkage Update';
    const MAIL_4 = '【DEV J PORTAL】 Project Linkage Update';
    const MAIL_5 = '【DEV J PORTAL】 Employee-Project Linkage Rejection';
    const MAIL_6 = '【DEV J PORTAL】 Employee-Project Linkage Approval';
    const MAIL_7 = '【DEV J PORTAL】 Employee-Project Linkage Update Rejection';
    const MAIL_8 = '【DEV J PORTAL】 Employee-Project Linkage Update Approval';
    
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
            ->view('mail.project', ['mailData' => $this->mailData, 'mailType' => $this->mailType]);
    }
}
