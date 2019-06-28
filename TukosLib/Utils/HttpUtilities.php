<?php
namespace TukosLib\Utils;
use TukosLib\TukosFramework as Tfk;

class HttpUtilities{
    public static function downloadFile($fileName, $contentType, $downloadToken){
        if (file_exists($fileName)){
            clearstatcache();
            self::setHeaderAndCookie(['name' => basename($fileName), 'type' => $contentType, 'size' => filesize($fileName)], $downloadToken);
            ob_end_clean();
            readfile($fileName);
        /*if ($fileHandle = fopen($fileName, 'r')){
            self::setHeaderAndCookie(['name' => basename($fileName), 'type' => $contentType, 'size' => filesize($fileName)], $downloadToken);
            while (!feof($fileHandle)){
                $buffer = fread($fileHandle, 2048);
                echo $buffer;
            }
            fclose($fileHandle);
            unlink($fileName);
            return false;// this is required so as not to send anything (e.g. Feedback) in the response after successful download*/
        }else{
            Feedback::add($this->tr('errorgeneratingfile'));
            return [];
        }
    }
    public static function setHeaderAndCookie($fileInfo, $downloadToken){
        header("Content-type:" . $fileInfo['type']);
        if (isset($fileInfo['mdate'])){
            header("Last-Modified:" . gmdate("D, d M Y H:i:s", strtotime($fileInfo['mdate'])) . " GMT");
        }
        header("Content-Length:" . $fileInfo['size']);
        header("Content-Disposition: attachment; filename=" . $fileInfo['name']);
        header("Content-Description: PHP Generated Data");
        setcookie('downloadtoken', $downloadToken, 0, '/');
    }    
}
?>
