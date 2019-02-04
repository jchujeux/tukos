<?php
/*
 *
 */
namespace TukosLib\Objects\Admin\Mail\TukosMessages;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel{

    protected $statusOptions = ['draft', 'sent', 'sendfailed'];
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'accountid'  => 'INT(11) DEFAULT NULL',
            'sender'     => 'VARCHAR(512)',
            'tos'        => 'VARCHAR(2048)',
            'ccs'        => 'VARCHAR(2048)',
            'bccs'       => 'VARCHAR(2048)',
            'status'     => "ENUM ('" . implode("','", $this->statusOptions) . "')",
            'statusdate' => "timestamp",
            'smtpserverid'=>'INT(11) DEFAULT NULL',
        ];
        parent::__construct($objectName, $translator, 'mailtukosmessages', ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects(), 'accountid' => ['mailaccounts'], 'smtpserverid' => ['mailsmtps']], [], $colsDefinition, [], ['status']);
        $this->getAccountsModel();
    }

    public function getAccountsModel(){
        if (empty($this->mailAccountsModel)){
            $this->objectsStore = Tfk::$registry->get('objectsStore');
            $this->mailAccountsModel = $this->objectsStore->objectModel('mailaccounts');
        }
    }
    protected function fromLabel($accountInfo){
        return $accountInfo['name'] . ' <' . $accountInfo['eaddress'] . '>';
    }
    public function setInits(){
        if (empty($this->userMailAccount)){
            $this->userMailAccount = $this->mailAccountsModel->userMailAccount('getOneExtended');
            if (!empty($this->userMailAccount)){
                $this->init = [
                    'parentid' => $this->user->id(),
                    'accountid' => $this->userMailAccount['id'], 'sender' => $this->fromLabel($this->userMailAccount), 'smtpserverid' => $this->userMailAccount['smtpserverid'],
                    'permission' =>  'RO', 'contextid' => $this->user->getContextid($this->objectName), 'status' => 'draft',
                ];
            }else{
                return parent::setInits();
            }
        }
    }
/*
    public function initialize($init=[]){
        $this->setInits();
        return array_merge ($this->init, $init) ;
    }
*/
    public function duplicateOneExtended($id, $cols, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $result = $this->getOneExtended(['where' => ['id' => $id], 'cols' => array_diff($cols, ['id', 'status', 'statusdate', 'created', 'creator', 'updated', 'updator', 'history'])]);
        $result['status'] = 'draft';
        $result['statusdate'] = null;
        return $result;
    }
    public function getAccountIdChanged($atts){
        $result = $this->mailAccountsModel->getOneExtended(['where' => ['id' => $atts['where']['accountid']], 'cols' => ['name', 'eaddress', 'smtpserverid']]);
        return ['accountid' => $atts['where']['accountid'], 'sender' => $this->fromLabel($result), 'smtpserverid' => $result['smtpserverid']];
    }

    public function processOne ($where){
        $mailArgs = $this->getOne(['where' => $this->user->filter($where, $this->objectName), 'cols' => ['id', 'accountid', 'name', 'sender', 'tos', 'ccs', 'bccs', 'smtpserverid','comments']]);
        $mailArgs['subject'] = Utl::extractItem('name', $mailArgs);
        $mailArgs['from'] = Utl::extractItem('sender', $mailArgs);
        $mailArgs['body'] = Utl::extractItem('comments', $mailArgs);
        $mailArgs['tos'] = explode(',', $mailArgs['tos']);
        if (!empty($mailArgs['ccs'])){
            $mailArgs['ccs'] = explode(',', $mailArgs['ccs']);
        }else{
            unset($mailArgs['ccs']);
        }
        if (!empty($mailArgs['bccs'])){
            $mailArgs['bccs'] = explode(',', $mailArgs['bccs']);
        }else{
            unset($mailArgs['bccs']);
        }
        $accountId = Utl::extractItem('accountid', $mailArgs);
        $accountInfo = $this->mailAccountsModel->getAccountInfo(['where' => ['id' => $accountId], 'cols' => ['eaddress', 'password', 'privacy', 'smtpserverid']]);
        $mailArgs['username'] = $accountInfo['username'];
        $mailArgs['password'] = $accountInfo['password'];
        $smtpModel  = $this->objectsStore->objectModel('mailsmtps');
        $smtpServerId = (!empty($mailArgs['smtpserverid']) ? $mailArgs['smtpserverid'] : $accountInfo['smtpserverid']);
        if ($smtpModel->send($smtpServerId, $mailArgs, true)){
            Feedback::add($this->tr('mail sent'));
            return $this->updateOne(['id' => $mailArgs['id'], 'status' => 'sent', 'statusdate' => date('Y-m-d H:i:s')]); 
        }else{
            Feedback::add($this->tr('mailsendfailed'));
            return $this->updateOne(['id' => $mailArgs['id'], 'status' => 'sendfailed', 'statusdate' => date('Y-m-d H:i:s')]);
        }
    }
}
?>
