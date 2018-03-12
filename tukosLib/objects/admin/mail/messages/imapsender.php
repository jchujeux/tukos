<?php
namespace TukosLib\Objects\Admin\Mail\Messages;
use TukosLib\Utils\Html2Text;

class ImapSender{

    function __construct(){
        $this->html2Text = new Html2Text();
    }
    function compose($args){

        if (substr($args['body'], 0, 6) !== '<html>'){
            $args['body'] = '<html><body>' . $args['body'] . '</body></html>';
        }
        
        $envelope['from'] = $args['from'];
        $envelope['to']   = $args['to'];
        
        $body[1]['type']    = TYPETEXT;
        $body[1]['subtype'] = 'plain';
        $body[1]['contents.data'] = $this->html2Text->convert($args['body']);
        
        $body[2]['type']    = TYPETEXT;
        $body[2]['subtype'] = 'html';
        $body[2]['contents.data'] = $args['body'];
        
        if (!empty($args['attachments'])){/* incomplete - See imap_mail_compose*/
            foreach ($args['attachments'] as $fileName){
                $part['type']     = TYPEAPPLICATION;
                $part['subtype']  = 'octet-stream';
                $part['encoding'] = ENCBASE64;
                $part['description'] = $fileName;
                $part['disposition.type'] = 'attachment';
                $part['disposition'] = ['filename' => $fileName];
                $part['dparameters.name'] = $fileName;
                $part['contents.data'] = base64_encode(fread($file_handle, $file_size));

                $body[] = $part;
            }
        }
        return imap_mail_compose($envelope, $body);
    }
    
    function send($args){
        $message = $this->compose($args);
        $success = imap_mail($args['to'], $args['subject'], $message);
        return $success;
    }
}
?>
