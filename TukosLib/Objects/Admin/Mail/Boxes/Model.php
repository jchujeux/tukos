<?php
/*
 *
 * class for the mail account tukos object
 */
namespace TukosLib\Objects\Admin\Mail\Boxes;

use TukosLib\Objects\Admin\Mail\AbstractModel;

use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel{

    function __construct($objectName, $translator=null){
        $colsDefinition = [
                'id'  =>  'VARCHAR(50)',
          'parentid'  =>  'INT(11)',
              'name'  =>  'VARCHAR(50)',
             'Nmsgs'  =>  'INT(11)',
            'Recent'  =>  'INT(11)',
            'Unread'  =>  'INT(11)',
           'Deleted'  =>  'INT(11)',
              'Size'  =>  'INT(11)',
        ];

        $this->accountIdCol   = 'parentid';
        $this->mailboxNameCol = 'name';

        parent::__construct($objectName, $translator);

        $this->allCols      = array_keys($colsDefinition);
        $this->nonImapCols  = ['id', $this->accountIdCol, $this->mailboxNameCol];
        $this->idProps       = [$this->accountIdCol, $this->mailboxNameCol];

        $objectsStore = Tfk::$registry->get('objectsStore');
        $this->mailAccountsObj = $objectsStore->objectModel('mailaccounts');
    }
    public function setInits(){
        if (empty($this->userMailAccount)){
            $this->userMailAccount = $this->mailAccountsObj->userMailAccount();
            $this->init         = [];//[$this->accountIdCol => $this->userMailAccount['id']];
        }
    }
    public function initialize($init=[]){
        $this->setInits();
        return array_merge ($this->init, $init);
        return $result;
    }

    public function initializeExtended($init=[]){
        return $init;
    }

    public function getAccountAndMailboxes($id, $force = false){
        if ($this->mailAccountsObj->getAccount($id)){
            if ($force || empty($this->mailAccountsObj->openedAccounts[$id]['mailboxes'])){
                $this->mailAccountsObj->openedAccounts[$id]['allboxes']    = @imap_getmailboxes($this->mailAccountsObj->openedAccounts[$id]['handle'], $this->mailAccountsObj->openedAccounts[$id]['mailboxPath'], '*');
                if (is_array($this->mailAccountsObj->openedAccounts[$id]['allboxes'])){
                    $mailboxes = [];
                    foreach ($this->mailAccountsObj->openedAccounts[$id]['allboxes'] as $key => $val){
                        if (!($val->attributes & LATT_NOSELECT) == LATT_NOSELECT){// does not work if strict equality used
                            //$mailboxes[] = str_replace($this->mailAccountsObj->openedAccounts[$id]['mailboxPath'], "", imap_utf7_decode($val->name));
                            $mailboxes[] = str_replace($this->mailAccountsObj->openedAccounts[$id]['mailboxPath'], "", $val->name);
                        }
                    }
                }
                $this->mailAccountsObj->openedAccounts[$id]['mailboxes'] = $mailboxes;
            }
            return $this->mailAccountsObj->openedAccounts[$id];
        }else{
            return false;
        }
    }
/*
    public function create($newValues, $init=false){//newValues are in extended form ('parentid')
        if (!empty($newValues['id'])){
            Feedback::add($this->tr('notanewmailbox'));
            return false;
        }else{
            if ($init){
                $this->setInits();
                $newValues = array_merge($this->init, $newValues);
            }
            if (empty($newValues['parentid']) || empty($newValues['name'])){
                Feedback::add('mailaccountandboxrequired');
                return false;
            }else if (!empty($newValues['parentid'])){
                $newValues['parentid'] = $newValues['parentid'];
            }
            $accountId   = $newValues['parentid'];
            $mailboxName = $newValues['name'];
            $account = $this->getAccountAndMailboxes($accountId);
            if ($account){
                if (in_array($mailboxName, $account['mailboxes'])){
                    Feedback::add('MailboxAlreadyExists');
                    return ['id' => $this->idString($newValues)];
                }else{
                    $fullMailboxName = $account['mailboxPath'] . $mailboxName;
                    if (@imap_createmailbox($account['handle'], $fullMailboxName)){
                        $account = $this->getAccountAndMailboxes($accountId, true);
                        imap_errors();
                        imap_alerts();
                        return ['id' => $this->idString($newValues)];
                    }else{
                        Feedback::add('CouldNotCreateMailbox');
                    }
                }
            }else{
                return false;
            }
        }
    }            
*/
    public function get($newValues, $init/*not used*/){
        if (empty($newValues['parentid']) || empty($newValues['name'])){
            Feedback::add('mailaccountandboxrequired');
            return false;
        }else if (!empty($newValues['parentid'])){
            $newValues['parentid'] = $newValues['parentid'];
        }
        $accountId   = $newValues['parentid'];
        $mailboxName = $newValues['name'];
        $account = $this->getAccountAndMailboxes($accountId);
        if ($account){
            if (in_array($mailboxName, $account['mailboxes'])){
                return ['id' => $this->idString($newValues)];
            }else{
                Feedback::add('MailboxDoesNotExists');
                return false;
            }
        }else{
            return false;
        }
    }            

    public function mailboxExists($accountId, $mailboxName){
        $account = $this->getAccountAndMailboxes($accountId);
        if ($account){
            return in_array($mailboxName, $account['mailboxes']);
        }else{
            return false;
        }
    }

    public function getMailbox($accountId, $mailboxName){
        $account = $this->getAccountAndMailboxes($accountId);
        if ($account){
            if (!empty($account['openedMailbox']) && $account['openedMailbox']['mailboxName'] === $mailboxName){
                return $account['openedMailbox'];
            }else{
                $this->mailAccountsObj->openedAccounts[$accountId]['openedMailbox'] = false;
                if (in_array($mailboxName, $account['mailboxes'])){
                    @imap_reopen($account['handle'], $account['mailboxPath'] . $mailboxName, CL_EXPUNGE);
                    $openedMailbox = [];
                    $openedMailbox['mailAddress']       = $account['eaddress'];
                    $openedMailbox['mailboxName']       = $mailboxName;
                    $openedMailbox['mailboxFullName']   = $account['mailboxPath'] . $mailboxName;
                    $openedMailbox['stream']            = $account['handle'];
                    $this->mailAccountsObj->openedAccounts[$accountId]['openedMailbox'] = $openedMailbox;
                    return $this->mailAccountsObj->openedAccounts[$accountId]['openedMailbox'];
                }else{
                    Feedback::add('CouldNotOpenMailBoxOnServer: ' . $mailboxName . ' ' . 'for accountId: ', $accountId);
                    return false;
                }
                imap_errors();
                imap_alerts();
            }
        }else{
            return false;
        }
    }  
    
    public function numMsgs($mailbox){
        return @imap_num_msg($mailbox['stream']);
    }          

    public function getOneImap($atts){
        $accountId   = $atts['where'][$this->accountIdCol];
        $mailboxName = $atts['where'][$this->mailboxNameCol];
        $mailbox     = $this->getMailbox($accountId, $mailboxName);
        if ($mailbox){
            $mailboxInfo = @imap_mailboxmsginfo($mailbox['stream']);
            $values = [];
            $cols = $atts['cols'];
            foreach ($cols as $col){
                $values[$col] = $mailboxInfo->$col;
            }
            return $values;
        }else{
            return [];
        }
    }
    
    public function getOneImapFromStatus($imap_stream, $mailbox){
        //$mailboxInfo = @imap_status($imap_stream, imap_utf7_encode($mailbox), SA_MESSAGES + SA_RECENT + SA_UNSEEN);
        $mailboxInfo = @imap_status($imap_stream,$mailbox, SA_MESSAGES + SA_RECENT + SA_UNSEEN);
        $cols = ['messages' => 'Nmsgs', 'recent' => 'Recent', 'unseen' => 'Unread'];
        foreach ($cols as $key => $col){
            $value[$col] = isset($mailboxInfo->$key) ? $mailboxInfo->$key : '';
        }
        return $value;
    }

    

    public function getAll ($atts, $jsonColsPaths = [], $jsonNotFoundValues = null){
        //$accountIds = $this->accountIds($atts['where']);
        $whereAccounts = (empty($atts['where']['parentid']) ? ['id' => 'default'] : ['id' => $atts['where']['parentid']]);
        if ($whereAccounts['id'] === 'default'){
            if (empty($this->userMailAccount)){
                $this->setInits();
            }
            $whereAccounts['id'] = $this->userMailAccount['id'];
        }
        $accountIds = array_column($this->mailAccountsObj->getAll(['where' => $whereAccounts, 'cols' => ['id']]), 'id');
        $this->splitRequestedCols($atts['cols'], $this->nonImapCols);
        $values = [];
        foreach ($accountIds as $accountId){
            $account = $this->getAccountAndMailboxes($accountId);
            $atts['where'][$this->accountIdCol] = $accountId;
            if ($account){
                foreach ($account['mailboxes'] as $mailboxName){
                    $atts['where'][$this->mailboxNameCol] = $mailboxName;
                    $atts['where']['id'] = $this->idString($atts['where']);
                    $value = $this->getOneNonImap(['where' => $atts['where'], 'cols' => $this->requestedNonImapCols]);
                    if ($this->requestedImapCols){
                        $value = array_merge($value, $this->getOneImapFromStatus($account['handle'], $account['mailboxPath'] . $mailboxName));
                    }
                    $values[] = $value;
                }
            }
        }
        $this->foundRows = count($values);
        return $values;
    }
    
}
?>
