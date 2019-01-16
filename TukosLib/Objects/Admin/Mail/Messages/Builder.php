<?php
namespace TukosLib\Objects\Admin\Mail\Messages;
use TukosLib\Utils\Html2Text;

class Builder{

    function __construct(){
        $this->html2Text = new Html2Text();
    }
    function build($args){

        if (!isset($args['body'])){
            $args['body'] = '';
        }
        if (substr($args['body'], 0, 6) !== '<html>'){
            $args['body'] = '<html><head></head><body>' . $args['body'] . '</body></html>';
        }
        $num = md5( time() );
        
        $rn = "\r\n";
        /*Define the main headers.*/
        $header = "From:" . $args['from'] . $rn;
        //$header = "To:" . $args['to'] . $rn;
        $header .= "MIME-Version: 1.0$rn";

        if (!empty($args['attachments'])){
            $header .= "Content-Type: multipart/mixed; boundary=\"php-mixed-$num\"$rn";
            $header .= "$rn--php-mixed-$num$rn";
        }


        $header .= "Content-Type: multipart/alternative; boundary=\"php-alt-$num\"$rn";
        $header .= "$rn--php-alt-$num$rn";            
            # Define the plain text message section
            $header .= "Content-Type: text/plain$rn";
            $header .= "Content-Transfer-Encoding:8bit$rn";
            $header .= $rn . $this->html2Text->convert($args['body']) . $rn;
            $header .= "--php-alt-$num\r\n";
            # Define the html message section
            $header .= "Content-Type: text/html$rn";
            $header .= "Content-Transfer-Encoding:8bit$rn";
            $header .= $rn . $args['body'] . $rn;
    
        $header .= "$rn--php-alt-$num--$rn";
    
        if (!empty($args['attachments'])){
            # Define the attachment section
            $header .= "--php-mixed-$num\r\n";
                $header .= "Content-Type:  multipart/mixed; ";
                $header .= "name=\"test.txt\"\r\n";
                $header .= "Content-Transfer-Encoding:base64\r\n";
                $header .= "Content-Disposition:attachment; ";
                $header .= "filename=\"test.txt\"\r\n\n";
                $header .= "$encoded_content\r\n";
            
            $header .= "--php-mixed-$num--\r\n";
        }
        return ['to' => (isset($args['to']) ? $args['to'] : ''), 'subject' => (isset($args['subject']) ? $args['subject'] : ''), 'body' => '', 'headers' => $header];
    }
}
?>
