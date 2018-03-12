<?php
namespace TukosLib\Objects\Admin\Mail\Smtps;

use PHPMailer\PhpMailer;
use Html2Text\Html2Text;
use TukosLib\Utils\Utilities as Utl;

class Sender{

    private $mailer;

    function send($smtpInfo, $mailArgs, $isHtml = true){
        if (!$this->mailer){
            $this->mailer = new PHPMailer;
            $this->mailer->CharSet = 'UTF-8';
        }
        $this->mailer->isSMTP();
        $this->mailer->Host = $smtpInfo['hostname'];
        if ($smtpInfo['auth'] === 'none'){
            $this->mailer->SMTPAuth = false;
        }else{
            $this->mailer->SMTPAuth = true;
            if ($smtpInfo['auth'] === 'specific'){
                $this->mailer->Username = $smtpInfo['smtpuser'];
                $this->mailer->Password = $smtpInfo['smtppwd'];
            }else{
                $this->mailer->Username = $mailArgs['username'];
                $this->mailer->Password = $mailArgs['password'];
            }
        }
        $this->mailer->Port = $smtpInfo['port'];
        if ($smtpInfo['security'] !== 'none'){
            $this->mailer->SMTPSecure = $smtpInfo['security'];
        }
        
        $mailArgValue     = ['from' => 'From', 'fromname' => 'FromName', 'subject' => 'Subject'];
        $mailArgFunction  = [
            'to' => [$this->mailer, 'addAddress'], 'replyto' => [$this->mailer, 'addReplyTo'], 'cc' => [$this->mailer, 'addCC'], 'bcc' => [$this->mailer, 'addBCC'],
            'tos' => [$this, 'addTos'], 'replytos' => [$this, 'addReplyTos'], 'ccs' => [$this, 'addCCs'], 'bccs' => [$this, 'addBCCs'],
            'attachments' => [$this, 'addAttachments'], 
        ];
        if (!empty($mailArgs['body'])){
            if ($isHtml){
                $this->mailer->isHtml(true);
                $this->mailer->msgHTML($mailArgs['body'], '', function($html){
                    return Html2Text::convert($html);
                });
            }else{
                $this->mailer->isHtml(false);
                $this->mailer->Body = $mailArgs['body'];
            }
        }else{
            $this->mailer->isHtml(false);
            $this->mailer->Body = ' ';
        }
        
        foreach ($mailArgValue as $valueKey => $mailerKey){
            if (!empty($mailArgs[$valueKey])){
                $this->mailer->$mailerKey = $mailArgs[$valueKey];
            }
        }
        foreach ($mailArgFunction as $valueKey => $mailerFunction){
            if (!empty($mailArgs[$valueKey])){
                call_user_func($mailerFunction, $mailArgs[$valueKey]);
            }
        }
        $this->mailer->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];// inserted although unsafe due to TDS desktop generating ssl error
        return $this->mailer->send();
    }

    function addAddresses($addresses, $method){
        foreach ($addresses as $address){
            if (is_array($address)){
                $this->mailer->$method($address[0], $address[1]);
            }else{
                $this->mailer->$method($address);
            }
        }
    }   

    function addTos($addresses){
        $this->addAddresses($addresses, 'addAddress');
    }   
    function addReplyTos($addresses){
        $this->addAddresses($addresses, 'addReplyTo');
    }   
    function addCCs($addresses){
        $this->addAddresses($addresses, 'addBCC');
    }   
    function addBCCs($addresses){
        $this->addAddresses($addresses, 'addAddress');
    }
    function addAttachments($attachments){
        foreach ($attachments as $attachment){
            if (is_array($attachment)){
                call_user_func_array([$this->mailer, 'addAttachment'],$attachment);
            }else{
                $this->mailer->addAttachment($attachment);
            }
        }
    }
}
?>
