<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

use App\Models\MailHistory;

class LogMail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $v  = (array) $event->sent;
        $mailData =  (array) $event->data['mailData'];
        $toArray =  (array) $event->message->getTo();
        $toArray =  (array) $toArray[0];
        $ctr=0;
        $emailTo = "";
        $emailToName = "";
        foreach ($toArray as $key => $val) {
            if ($ctr == 0) {
                $emailTo = $val;
                $ctr++;
            } else {
                $emailToName = $val;
            }
        }
        $toArray =  (array) $event->message->getFrom();
        $toArray =  (array) $toArray[0];
        $ctr=0;
        $emailFrom = "";
        $emailFromName = "";
        foreach ($toArray as $key => $val) {
            if ($ctr == 0) {
                $emailFrom = $val;
                $ctr++;
            } else {
                $emailFromName = $val;
            }
        }
        $toArray =  (array) $event->message->getBody();
        $ctr=0;
        $body = "";
        foreach ($toArray as $key => $val) {
            if ($ctr == 1) {
                $body = $val;
            } else if ($ctr > 1) {
                break;
            }
            $ctr++;
        }

         MailHistory::create([
            'subject' => $event->message->getSubject(),
            'to' => $emailTo,
            'to_name' => $emailToName,
            'from' => $emailFrom,
            'from_name' => $emailFromName,
            'body' => $body,
            'created_by' => $mailData['currentUserId'],
            'updated_by' => $mailData['currentUserId'],
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
            'module' => $mailData['module'],
        ]);
    }

}
