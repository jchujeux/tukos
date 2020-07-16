<?php
namespace TukosLib\Objects\Admin\Mail\Servers;

use TukosLib\Objects\AbstractModel;

use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel{

    protected $protocolOptions = ['imap', 'pop3'];
    protected $portOptions     = ['143', '465', '993', '995'];
    protected $securityOptions = ['none', 'ssl', 'ssl/novalidate-cert'];
    protected $authOptions     = ['normalpwd'];
    protected $softwareOptions = ['Mercury', 'unknown'];

    function __construct($objectName, $translator=null){
        $colsDefinition = [ 
            'hostname'  =>  'VARCHAR(255)  DEFAULT NULL',
            'protocol'  =>  "ENUM ('" . implode("','", $this->protocolOptions) . "')",
            'port'      =>  "ENUM ('" . implode("','", $this->portOptions) . "')",
            'security'  =>  "ENUM ('" . implode("','", $this->securityOptions) . "')",
            'auth'      =>  "ENUM ('" . implode("','", $this->authOptions) . "')",
            'software'  =>  "ENUM ('" . implode("','", $this->softwareOptions) . "')",
            'adminpwd'  =>  'VARCHAR(255)  DEFAULT NULL',
        ];
        parent::__construct($objectName, $translator, 'mailservers', ['parentid' => ['organizations', 'itsystems', 'people']], [], $colsDefinition, [], ['security', 'auth', 'software']);
    }
    public function getOne ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null, $absentColsFlag = 'forbid'){
        $result = parent::getOne($atts, $jsonColsPaths, $jsonNotFoundValue, $absentColsFlag);
        if (!empty($result['adminpwd'])){
            $result['adminpwd'] = $this->user->decrypt($result['adminpwd'], 'shared');
        }
        return $result;
    }
    function mailboxPath($id){
            $serverInfo  = $this->getOne(['where' => ['id' => $id], 'cols' => ['hostname', 'protocol', 'port', 'security', 'auth']]);
            return '{' . $serverInfo['hostname'] . ':' . $serverInfo['port'] . '/' . $serverInfo['protocol'] . ($serverInfo['security'] === 'none' ? '}' : '/' . $serverInfo['security'] . '}');
    }
}
?>
