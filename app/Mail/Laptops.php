<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Laptops extends Mailable
{
    use Queueable, SerializesModels;

    protected $mailType;
    protected $mailData;
    const MAIL_1 = '【DEV J PORTAL】Laptop Registation';
    const MAIL_2 = '【DEV J PORTAL】Laptop Registation Rejection';
    const MAIL_3 = '【DEV J PORTAL】Laptop Registration Approval';
    const MAIL_4 = '【DEV J PORTAL】Laptop Detail Update';
    const MAIL_5 = '【DEV J PORTAL】Laptop Detail Update Approval';
    const MAIL_6 = '【DEV J PORTAL】Laptop Detail Update Rejection';
    const MAIL_7 = '【DEV J PORTAL】Laptop Linkage';
    const MAIL_8 = '【DEV J PORTAL】Laptop Linkage Approval';
    const MAIL_9 = '【DEV J PORTAL】Laptop Linkage Rejection';
    const MAIL_10 = '【DEV J PORTAL】Laptop Linkage';
    const MAIL_11 = '【DEV J PORTAL】Laptop Linkage Update';
    const MAIL_12 = '【DEV J PORTAL】Laptop Linkage Update Approval';
    const MAIL_13 = '【DEV J PORTAL】Laptop Linkage Update Rejection';
    const MAIL_14 = '【DEV J PORTAL】Laptop Linkage Update';
    const MAIL_15 = '【DEV J PORTAL】Laptop Registration Cancellation';
    const MAIL_16 = '【DEV J PORTAL】Laptop Detail Update Cancellation';
    const MAIL_17 = '【DEV J PORTAL】Laptop Linkage Cancellation';
    const MAIL_18 = '【DEV J PORTAL】Laptop Linkage Update Cancellation';

    
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
        $subject = env('APP_ENV') != 'production' ? "【" . strtoupper(env('APP_ENV')) . "】".constant("self::MAIL_{$this->mailType}") : constant("self::MAIL_{$this->mailType}");
        return $this
            ->subject($subject)
        ->view('mail.laptop', ['mailData' => $this->mailData, 'mailType' => $this->mailType]);
    }
}
