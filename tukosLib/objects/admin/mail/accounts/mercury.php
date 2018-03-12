<?php

namespace TukosLib\Objects\Admin\Mail\Accounts;

use TukosLib\TukosFramework as Tfk;

class Mercury  {
   
    function __construct($mailServerFolder, $serverInfo){
        $this->serverFolder = $mailServerFolder;
        $this->usersFolder  = $this->serverFolder. 'MAIL/';
        $this->serverInfo = $serverInfo;
    }
   /*
    * Creates the account if needed, and/or sets new password, or error (specific to Mercury mail server)
    */
    function processOne($accountInfo){
        $parts = explode('@', $accountInfo['eaddress']);
        $newUserName = strtolower($parts[0]);
        $pmailFileName     = $this->usersFolder . 'PMAIL.usr';
        $userMailFolder = $this->usersFolder . $accountInfo['username'];
        if (file_exists($userMailFolder)){
            $pmailFileName     = $this->usersFolder . 'PMAIL.usr';
            $tempPmailFileName = $this->usersFolder . 'PMAIL.tmp';
            if (file_exists($pmailFileName)){
                $handleR = fopen($pmailFileName    , 'r');
                $handleW = fopen($tempPmailFileName, 'w');
                $replaced = false;
                $feedback = 'ErrorUserNotFoundInPmail';
                while (!feof($handleR)){
                    $line = fgets($handleR);
                    if (!empty($line)){
                        $elements = explode(";", $line);
                        $role          = $elements[0];
                        $username      = strtolower($elements[1]);
                        $personal_name = $elements[2];
                        if ($accountInfo['username'] === $username) {
                            $feedback = 'OK';
                            if ($accountInfo['name'] !== $personal_name){
                                $replaced = true;
                            }
                            fwrite($handleW, $role .';'. $username .';'. $accountInfo['name']);
                            $feedback = $this->changePassword($userMailFolder, $accountInfo['password']);
                        }else{
                            fwrite($handleW, $line);
                        }
                    }
                }
                fclose($handleR);
                fclose($handleW);
                if ($replaced){
                    rename($tempPmailFileName, $pmailFileName);
                }else{
                    unlink($tempPmailFileName);
                }
            }else{
                return 'ErrorNoPmailUsrFile';
            }            
        }else{
            $feedback = $this->createAccount($accountInfo['username'], $accountInfo['name'], 'U', $accountInfo['password'], $pmailFileName, $userMailFolder);
        }
        if ($feedback === 'OK'){
            return $this->resetUsers();
        }else{
            return $feedback;
        }       
    }
    
    function createAccount($username, $personalName, $role, $password, $pmailFileName, $userMailFolder){
        $line = $role . ';' . $username. ';' . $personalName;
        $handle = fopen($pmailFileName, "a");
        if ($handle) {
           fwrite($handle, $line . chr(10));
           fclose($handle);
        }else{
            return 'CoundNotOpenPmail.Usr';
        }
        mkdir($userMailFolder);
        return $this->createPassword($userMailFolder, $password);
    }
    
    function createPassword($userMailFolder, $password){
        if ($password === ''){
            $feedback = 'blankPwdNotAllowed';
        }else{
            /*
             * 1. Mail server password
             */
            $line1 = '# Mercury/32 User Information File';
            $line2 = 'POP3_access: ' . $password;
            $line3 = 'APOP_secret: ' . $password;
            $filename = $userMailFolder .'\\PASSWD.PM';
            $handle = fopen($filename, "w");
            if ($handle) {
               fwrite($handle, $line1 . chr(10));
               fwrite($handle, $line2 . chr(10));
               fwrite($handle, $line3 . chr(10));
               fclose($handle);
               return 'OK';
            }else{
                return 'CoundNotCreatePASSWD.PM';
            }
        }
    }
    

    function changePassword($userMailFolder, $password){
        return $this->createPassword($userMailFolder, $password);
    }
    
    function resetUsers(){
        $to="maiser@" . $this->serverInfo['hostname'];
        $subject=".";
        $header="From: Admin@"  .  $this->serverInfo['hostname'];
        $message="
         password " . $this->serverInfo['adminpwd'] . "
         reload users
         exit
        ";
        ini_set('smtp', $this->serverInfo['hostname']);
        ini_set('smtp_port', 25);
        $sentmail = mail($to,$subject,$message,$header);
        if($sentmail){
           return 'OK';
        } else {
           return 'ResetUsersFailed';
        }
    }
}
?>
