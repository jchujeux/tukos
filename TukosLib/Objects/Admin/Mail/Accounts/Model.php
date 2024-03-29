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
    	                 'gmailtoken'    => 'VARCHAR(1023)  DEFAULT NULL ',
        ];
        parent::__construct($objectName, $translator, 'mailaccounts', ['parentid' => ['users'], 'smtpserverid' => ['mailsmtps'], 'mailserverid' => ['mailservers']], [], $colsDefinition);
        $this->openedAccounts = [];

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
}
?>
