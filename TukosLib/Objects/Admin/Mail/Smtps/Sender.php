<?php
namespace TukosLib\Objects\Admin\Mail\Smtps;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\OAuth;
//use League\OAuth2\Client\Provider\Google;
use TukosLib\Google\Client as GoogleClient;
use TukosLib\Google\Gmail;
use Html2Text\Html2Text;

class Sender{

    private $mailer;

    function send($smtpInfo, $mailArgs, $isHtml = true){
        if ($smtpInfo['auth'] === 'oauth'){
            return $this->sendViaGoogleGmailApi($smtpInfo, $mailArgs, $isHtml);
        }else{
            return $this->sendViaPhpMailer($smtpInfo, $mailArgs, $isHtml);
        }
    }
    function sendViaGoogleGmailApi($smtpInfo, $mailArgs, $isHtml = true){
        $boundary = uniqid(rand(), true);
        $strRawMessage = 
              "To: {$mailArgs['tos'][0]}\r\n"
            . "From: toto\r\n"
            . "Subject:=?utf-8?B?" . base64_encode($mailArgs['subject']) . "?=\r\n"
            //. "Subject:{$mailArgs['subject']}\r\n"
            . (isset($mailArgs['ccs']) ? "Cc: {$mailArgs['ccs'][0]}\r\n" : "")
            . "MIME-Version: 1.0\r\n";
        if (isset($mailArgs['attachments'])){
            foreach($mailArgs['attachments'] as $fullFileName){
                $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fullFileName);
                $fileName = basename($fullFileName);
                $strRawMessage .= 
                    "Content-type: Multipart/Mixed; boundary=\"$boundary\"\r\n"
                  . "\r\n--{$boundary}\r\n"
                  . "Content-Type:$mimeType; name=$fileName;\r\n"
                  //. "Content-ID: <jchujeux@gmail.com>\r\n"
                  . "Content-Description:$fileName;\r\n"
                  . "Content-Disposition: attachment; filename=\"$fileName\" size=" . filesize($fullFileName) . ";\r\n"
                  . "Content-Transfer-Encoding: base64\r\n\r\n"
                      . chunk_split(base64_encode(file_get_contents($fullFileName)), 76, "\n") . "\r\n"
                  . "--{$boundary}\r\n";
            }
        }
        $strRawMessage .=
              "Content-Type: text/html; charset=utf-8\r\n"
            . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
            . quoted_printable_encode($mailArgs['body']) . "\r\n";
        try {
            $mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
            $msg = new \Google_Service_Gmail_Message();
            $msg->setRaw($mime);
            $token = $mailArgs['gmailtoken'];
            $client = GoogleClient::get($mailArgs['googlecredentials']);
            $GmailService = Gmail::getService($token);
            return $GmailService->users_messages->send("me", $msg);
            
        } catch (\Exception $e) {
            $this->googleSendError = $e->getMessage();
            //unset($_SESSION['access_token']);
        }
    }
    function sendViaPhpMailer($smtpInfo, $mailArgs, $isHtml = true){
        if (!$this->mailer){
            $this->mailer = new PHPMailer;
            $this->mailer->CharSet = 'UTF-8';
        }
        $this->mailer->isSMTP();
        $this->mailer->Host = $smtpInfo['hostname'];
        switch($smtpInfo['auth']){
            case 'none':
                $this->mailer->SMTPAuth = false;
                break;
            case 'emailuser':
                $this->mailer->SMTPAuth = true;
                $this->mailer->Username = $mailArgs['username'];
                $this->mailer->Password = $mailArgs['password'];
                break;
            case 'specific':
                $this->mailer->SMTPAuth = true;
                $this->mailer->Username = $smtpInfo['smtpuser'];
                $this->mailer->Password = $smtpInfo['smtppwd'];
                break;
            /*case 'oauth':
                $this->mailer->SMTPAuth = true;
                $this->mailer->AuthType = 'XOAUTH2';
                $credentials = json_decode(file_get_contents('f:\tukos\credentials.json'), true);
                $token = json_decode(file_get_contents('f:\tukos\token.json'), true);
                $client = GoogleClient::get($credentials);
                $GmailService = Gmail::getService($token);
                $myOauth = new OAuth(['provider' => $client, 'clientId' => $credentials['web']['client_id'], 'clientSecret' => $credentials['web']['client_secret'],
                    'refreshToken' => $token['refresh_token'], 'token' => $token, 'userName' => 'me']);
                $this->mailer->setOAuth($myOauth);*/
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
                    return @Html2Text::convert($html);
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
    
    function getErrorInfo(){
        return $this->mailer ? $this->mailer->ErrorInfo : $this->googleSendError;
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
