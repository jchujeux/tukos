<?php
namespace TukosLib\Objects\Admin\Mail\Messages;
use TukosLib\Objects\Admin\Mail\Messages\Builder;

class Drafter extends Builder{

    function save($mailbox, $args){

        $messageArray = $this->build($args);
        $rn = "\r\n";
        $rn = "\r\n";
        $toAndSubject = 'to:' . $messageArray['to']. $rn .  'subject:' . $messageArray['subject'] . $rn;
        $success = imap_append($mailbox['stream'], $mailbox['mailboxFullName'], $toAndSubject . $messageArray['headers'], '\\Draft');
        return $success;
    }
}
?>
