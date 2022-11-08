<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Google\Client;
use TukosLib\Google\Gmail;
use TukosLib\TukosFramework as Tfk;

class TestGmailAPI {
    
    function __construct($parameters){
        try{
            $client = Client::get('f:\tukos\credentials.json');
            $tokenSource = 'f:\tukos\tokentukosbackoffice.json';
            $token = json_decode(file_get_contents($tokenSource), true);
            $service = Gmail::getService($token);
            try{
                $strMailContent = 'This is a test mail which is <b>sent via</b> using Gmail API client library.<br/><br/><br/>Thanks,<br/><b>Premjith K.K..</b>';
                // $strMailTextVersion = strip_tags($strMailContent, '');
                
                $strRawMessage = "";
                $boundary = uniqid(rand(), true);
                $subjectCharset = $charset = 'utf-8';
                $strToMailName = 'toto';
                $strToMail = 'jchujeux@free.fr';
                $strSubject = 'Test mail using GMail API - with attachment - ' . date('M d, Y h:i:s A');
                
                //$strRawMessage .= 'To: ' .$strToMailName . " <" . $strToMail . ">" . "\r\n";
                $strRawMessage .= 'To: ' . $strToMail .  "\r\n";
                
                $strRawMessage .= 'Subject: =?' . $subjectCharset . '?B?' . base64_encode($strSubject) . "?=\r\n";
                /*$strRawMessage .= 'MIME-Version: 1.0' . "\r\n";
                $strRawMessage .= 'Content-type: Multipart/Mixed; boundary="' . $boundary . '"' . "\r\n";
                
                $filePath = 'abc.pdf';
                $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
                $mimeType = finfo_file($finfo, $filePath);
                $fileName = 'abc.pdf';
                $fileData = base64_encode(file_get_contents($filePath));
                
                $strRawMessage .= "\r\n--{$boundary}\r\n";
                $strRawMessage .= 'Content-Type: '. $mimeType .'; name="'. $fileName .'";' . "\r\n";
                $strRawMessage .= 'Content-ID: <' . $strSesFromEmail . '>' . "\r\n";
                $strRawMessage .= 'Content-Description: ' . $fileName . ';' . "\r\n";
                $strRawMessage .= 'Content-Disposition: attachment; filename="' . $fileName . '"; size=' . filesize($filePath). ';' . "\r\n";
                $strRawMessage .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
                $strRawMessage .= chunk_split(base64_encode(file_get_contents($filePath)), 76, "\n") . "\r\n";
                $strRawMessage .= "--{$boundary}\r\n";*/
                $strRawMessage .= 'Content-Type: text/html; charset=' . $charset . "\r\n";
                $strRawMessage .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
                $strRawMessage .= $strMailContent . "\r\n";
                
                //Send Mails
                //Prepare the message in message/rfc822
                try {
                    // The message needs to be encoded in Base64URL
                    $mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
                    $msg = new \Google_Service_Gmail_Message();
                    $msg->setRaw($mime);
                    $objSentMsg = $service->users_messages->send("tukosbackoffice@gmail.com", $msg);
                    
                    print('Message sent object');
                    //print($objSentMsg);
                } catch (\Exception $e) {
                    print($e->getMessage());
                    unset($_SESSION['access_token']);
                }
            }
            catch(\Exception $e) {
                // TODO(developer) - handle error appropriately
                echo 'Message: ' .$e->getMessage();
            }echo 'done';
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>