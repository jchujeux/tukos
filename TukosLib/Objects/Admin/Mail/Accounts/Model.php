<?php
/*
 *
 * class for the mail account tukos object
 */
namespace TukosLib\Objects\Admin\Mail\Accounts;

use TukosLib\Objects\AbstractModel;

use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    protected $privacyOptions = ['private', 'shared'];
    function __construct($objectName, $translator=null){
    	$colsDefinition = ['eaddress'      =>  'VARCHAR(255) DEFAULT NULL ',
                         'username'      =>  'VARCHAR(255) DEFAULT NULL ',
                         'password'      =>  'VARCHAR(255) DEFAULT NULL ',
                         'privacy'       => "ENUM ('" . implode("','", $this->privacyOptions) . "') ",
                         'smtpserverid'  => 'INT(11) DEFAULT NULL',
                         'mailserverid'  => 'INT(11) DEFAULT NULL',
                         'draftsfolder'  => 'VARCHAR(255)  DEFAULT NULL ',
        ];
        parent::__construct($objectName, $translator, 'mailaccounts', ['parentid' => ['users'], 'smtpserverid' => ['mailsmtps'], 'mailserverid' => ['mailservers']], [], $colsDefinition);
        $this->openedAccounts = [];

        $this->mailConfig = Tfk::$registry->get('appConfig')->mailConfig;
        $this->init = ['draftsfolder' => 'Drafts'];
    }

    public function userMailAccount($getOne='getOne'){
        if (empty($this->userMailAccount)){
            $this->userMailAccount = $this->$getOne(['where' => ['parentid' => $this->user->id()], 'cols' => ['id', 'name', 'eaddress', 'username', 'smtpserverid', 'draftsfolder']]);
            return $this->userMailAccount;
        }else{
            return $this->userMailAccount;
        }
    }
    public function getAccountInfo($atts){
      if (in_array('password', $atts['cols']) && !in_array('privacy', $atts['cols'])){
      	$atts['cols'][] = 'privacy';
      }
      $result = $this->getOne($atts);
      if (!empty($result['password'])){
        $result['password'] = $this->user->decrypt($result['password'], $result['privacy']);
      }
      return $result;
    }

    public function getAccount($id){
        if (isset($this->openedAccounts[$id])){
            return $this->openedAccounts[$id];
        }else{
            $openingAccount = $this->getAccountInfo(['where' => ['id' => $id], 'cols' => ['id', 'name', 'eaddress', 'username', 'password', 'privacy', 'mailserverid', 'smtpserverid']]);
            if (!empty($openingAccount['mailserverid'])){
                $objectsStore = Tfk::$registry->get('objectsStore');
                $serverObj  = $objectsStore->objectModel('mailservers');
                $openingAccount['mailboxPath'] = $serverObj->mailboxPath($openingAccount['mailserverid']);
                $openingAccount['handle']      = @imap_open($openingAccount['mailboxPath'], $openingAccount['username'], $openingAccount['password'], OP_HALFOPEN | OP_SILENT);
                if ($openingAccount['handle']){
                    $this->openedAccounts[$id] = $openingAccount;
                }else{
                    Feedback::add('CouldNotOpenAccount', $id);
                    $this->openedAccounts[$id] = false;
                    imap_errors();
                    imap_alerts();
                }
            }
        }
        return $this->openedAccounts[$id];
    }

    /*
     * Creates or updates the account on tukos mail server (currently supports only Mercury server)
     */    
    function processOne($where){
        $accountInfo = $this->getAccountInfo(['where' => $where, 'cols' => ['id', 'name', 'eaddress', 'password', 'privacy', 'mailserverid']]);
        if (!empty($accountInfo['mailserverid'])){
            $objectsStore = Tfk::$registry->get('objectsStore');
            $serverObj  = $objectsStore->objectModel('mailservers');
            $serverInfo  = $serverObj->getOne(['where' => ['id' => $accountInfo['mailserverid']], 'cols' => ['id', 'name', 'hostname', 'protocol', 'port', 'security', 'auth', 'software', 'adminpwd']]);
            if ($serverInfo['hostname'] === $this->mailConfig['host'] && $serverInfo['software'] === $this->mailConfig['software']){
                try{
                    $accountMgtClass  = 'TukosLib\\Objects\\Admin\\Mail\\Accounts\\' . $serverInfo['software'];
                    $this->accountMgt = new $accountMgtClass(Tfk::mailServerFolder, $serverInfo);
                }catch(\Exception $e){
                    $accountMgtClass  = 'TukosLib\\Objects\\Admin\\Mail\\Accounts\\' . 'Unsupported';
                    $this->accountMgt = new $accountMgtClass(Tfk::mailServerFolder);
                }            
        
                return $this->accountMgt->processOne($accountInfo);
            }else{
                Feedback::add('NotTukosMailServer');
            }
        }else{
            Feedback::add('nomailserverdefined');
        }
    }
}
?>
