<?php

namespace App\Listeners;

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

        //get from data
        $fromArray =  (array) $event->message->getFrom();
        $fromArray = (array) $fromArray[0];
        $ctr=0;
        $emailFrom = "";
        $emailFromName = "";
        foreach ($fromArray as $key => $val) {
            if ($ctr == 0) {
                $emailFrom = $val;
                $ctr++;
            } else {
                $emailFromName = $val;
            }
        }
        //get mail body
        $mailBody =  (array) $event->message->getBody();
        $ctr=0;
        $body = "";
        foreach ($mailBody as $key => $val) {
            if ($ctr == 1) {
                $body = $val;
            } else if ($ctr > 1) {
                break;
            }
            $ctr++;
        }

        //create log for all recipients
        $mailData =  (array) $event->data['mailData'];
        $toArray =  (array) $event->message->getTo();
        foreach($toArray as $idx => $recipient){

            
            $recipient = (array) $recipient;
            $ctr=0;
            $emailTo = '';
            $emailToName = '';
            foreach($recipient as $key => $data){
                if ($ctr == 0) {
                    $emailTo = $data;
                    $ctr++;
                } else {
                    $emailToName = $data;
                }
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

}
