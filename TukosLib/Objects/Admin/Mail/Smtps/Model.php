<?php
namespace TukosLib\Objects\Admin\Mail\Smtps;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Admin\Mail\Smtps\Sender;
use TukosLib\Utils\Utilities as Utl;

use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel{

    //protected $protocolOptions = ['25/smtp', '465/smtp/ssl', '465/smtp/pwd'];
    protected $portOptions     = ['25', '465', '587'];
    protected $securityOptions = ['none', 'ssl', 'tls'];
    protected $authOptions     = ['none', 'emailuser', 'specific'];
    protected $sender;

    function __construct($objectName, $translator=null){
        $colsDefinition = [ 
            'hostname'  =>  'VARCHAR(255)  DEFAULT NULL ',
            //'protocol'  =>  "ENUM ('" . implode("','", $this->protocolOptions) . "') ",
            'port'      =>  "ENUM ('" . implode("','", $this->portOptions) . "') ",
            'security'  =>  "ENUM ('" . implode("','", $this->securityOptions) . "') ",
            'auth'      =>  "ENUM ('" . implode("','", $this->authOptions) . "') ",
            'smtpuser'  =>  'VARCHAR(255)  DEFAULT NULL ',
            'smtppwd'   =>  'VARCHAR(255)  DEFAULT NULL ',
        ];
        parent::__construct($objectName, $translator, 'mailsmtps', ['parentid' => ['organizations', 'itsystems', 'people']], [], $colsDefinition, [], ['security', 'auth']);
        $this->mailer = false;
    }
    public function getOne ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null, $absentColsFlag = 'forbid'){
        $result = parent::getOne($atts, $jsonColsPaths, $jsonNotFoundValue, $absentColsFlag);
        if (!empty($result['smtppwd'])){
            $result['smtppwd'] = $this->user->decrypt($result['smtppwd'], 'shared');
        }
        return $result;
    }

    function send($smtpId, $mailArgs, $isHtml = true){
        if (!$this->sender){
            $this->sender = new Sender;
        }
        if (empty($this->smtpInfo['id']) || $smtpId !== $this->smtpInfo['id']){
            $this->smtpInfo = $this->getOne(['where' => ['id' => $smtpId], 'cols' => ['id', 'hostname', 'port', 'security', 'auth', 'smtpuser', 'smtppwd']]);
        }
        if (empty($mailArgs['fromname'])){
            $pos = strpos($mailArgs['from'], '<');
            if ($pos){
                $mailArgs['fromname'] = substr($mailArgs['from'], 0, $pos);
                $endPos = strpos($mailArgs['from'], '>');
                $mailArgs['from'] = substr($mailArgs['from'], $pos+1, $endPos-$pos-1);
            }
        }
        $success = $this->sender->send($this->smtpInfo, $mailArgs, $isHtml);
        if ($success){
            Feedback::add($this->tr('Mailsent'));
        }else{
            Feedback::add($this->tr('Mailcouldnotbesent') . ': ' . $this->sender->getErrorInfo());
        }
        return $success;
    }
}
?>
