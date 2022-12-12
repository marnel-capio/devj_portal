<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Laptops extends Mailable
{
    use Queueable, SerializesModels;

    protected $mailType;
    protected $mailData;
    const MAIL_1 = '【DEV J PORTAL】Laptop Registation';
    const MAIL_4 = '【DEV J PORTAL】Laptop Detail Update';
    const MAIL_7 = '【DEV J PORTAL】Laptop Linkage Request';
    const MAIL_10 = '【DEV J PORTAL】Laptop Linkage';
    const MAIL_11 = '【DEV J PORTAL】Laptop Linkage Update Request';
    const MAIL_14 = '【DEV J PORTAL】Laptop Linkage Update';



    
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
        ->view('mail.laptop', ['mailData' => $this->mailData, 'mailType' => $this->mailType]);
    }
}
