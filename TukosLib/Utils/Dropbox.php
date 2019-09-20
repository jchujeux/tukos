<?php
namespace TukosLib\Utils;

use TukosLib\TukosFramework as Tfk;
use Dropbox\Dropbox as DropboxSdk;

class Dropbox{

    public static function downloadFile($dropboxFilePath, $accessToken){/* downloads the dropbox file in tukos temp directory, keeping the same file name*/
        //echo 'entering dropbox.downloadFile';
        $dropbox = new DropboxSdk($accessToken);
        $downloadedString = $dropbox->files->download('/' . $dropboxFilePath, false);
        if (substr($downloadedString, 0, 5) === "Error"){
            Feedback::add('error - invalid token(?)');
            return false;
        }else if (substr($downloadedString, 2, 5) === 'error'){
            Feedback::add('error - file path not found (?)');
            return false;
        }else{
            $fileName = substr(strchr($dropboxFilePath, '/'), 1);
            $localFilePath = Tfk::$tukosTmpDir . $fileName;
            $file = fopen($localFilePath, 'w');
            fwrite($file, $downloadedString);
            fclose($file);
            return $localFilePath;
        }
    }
    public static function uploadFile($dropboxFilePath, $accessToken){/* does the opposite of downloadDropboxFile*/
        $dropbox = new DropboxSdk($accessToken);
        $fileName = substr(strchr($dropboxFilePath, '/'), 1);
        $localFilePath = Tfk::$tukosTmpDir . $fileName;
        return $dropbox->files->upload('/' . $dropboxFilePath, $localFilePath, "overwrite");
    }
}
?>    
