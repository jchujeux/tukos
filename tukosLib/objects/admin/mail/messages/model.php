<?php
/*
 *
 * class for the mail account tukos object
 */
namespace TukosLib\Objects\Admin\Mail\Messages;

use TukosLib\Objects\Admin\Mail\AbstractModel;
use TukosLib\Objects\Admin\Mail\Messages\Builder as MessageBuilder;
use TukosLib\Objects\Admin\Mail\Messages\Sender as MailSender;
use TukosLib\Objects\Admin\Mail\Messages\Drafter as MailDrafter;
use TukosLib\Objects\Admin\Mail\Messages\Getter as MailGetter;

use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel{

    function __construct($objectName, $translator=null){
        $colsDefinition = [
                'id'  =>  'VARCHAR(50) ',
         'parentid'  =>  'INT(11) ',
       'mailboxname'  =>  'VARCHAR(50) ',
           'name'  =>  'VARCHAR(512) ',
              'from'  =>  'VARCHAR(512) ',
                'to'  =>  'VARCHAR(512) ',
              'date'  =>  'VARCHAR(50) ',
              'size'  =>  'INT(11) ',
               'uid'  =>  'INT(11) ',
             'msgno'  =>  'INT(11) ',
            'recent'  =>  'INT(11) ',
           'flagged'  =>  'INT(11) ',
          'answered'  =>  'INT(11) ',
           'deleted'  =>  'INT(11) ',
              'seen'  =>  'INT(11) ',
             'draft'  =>  'INT(11) ',
             'udate'  =>  'INT(11) ',
              'body'  =>  'longtext ',
        ];

        $this->accountIdCol   = 'parentid';
        $this->mailboxNameCol = 'mailboxname';

        parent::__construct($objectName, $translator);

        $this->allCols = array_keys($colsDefinition);
        $this->idColsObjects = ['parentid' => ['mailaccounts']]; 
        $this->idCols = array_keys($this->idColsObjects);

        $this->nonImapCols  = ['id', $this->accountIdCol, $this->mailboxNameCol];
        $this->idProps      = [$this->accountIdCol, $this->mailboxNameCol, 'uid'];

        $objectsStore = Tfk::$registry->get('objectsStore');
        $this->mailAccountsObj = $objectsStore->objectModel('mailaccounts');
        $this->mailboxesObj = $objectsStore->objectModel('mailboxes');
        $this->mailMessage = new MailGetter();
    }

    protected function fromLabel($accountInfo){
        return $accountInfo['name'] . ' <' . $accountInfo['eaddress'] . '>';
    }
/*
    public function setInits(){
        if (empty($this->userMailAccount)){
            $this->userMailAccount = $this->mailAccountsObj->userMailAccount();
            $this->init = [
                $this->accountIdCol => $this->userMailAccount['id'], 'mailboxname' => $this->userMailAccount['draftsfolder'], 'from' => $this->fromLabel($this->userMailAccount), 'draft' => 1
            ];
        }
    }
    public function initialize($init=[]){
        $this->setInits();
        return array_merge ($this->init, $init) ;
    }
*/
   public function imapFetchOverviewToTukos($values){
        foreach ($values as $col => $value){
            switch($col){
                case 'udate' :
                    $values[$col] = date('Y-m-d H:i:s', $values[$col] );
                    break;
                default:
                    break;
            }
        }
        return $values;
    }
    
    public function imapGetToTukos($message){
        $result = [];
        $tukosToImapGetCols = ['date' => 'date', 'name' => 'subject', 'to' => 'toaddress', 'from' => 'fromaddress'];
        foreach ($this->requestedImapCols as $col){
            switch($col){
                case 'body' : 
                    $result['body'] = (empty($message['body']['textHtml']) ? $message['body']['textPlain'] : $message['body']['textHtml']);
                    break;
                case 'attachments' :
                    $result['attachments'] = $message['attachments'];
                    break;
                default:
                    $imapCol = (empty($tukosToImapGetCols[$col]) ? $col : $tukosToImapGetCols[$col]);
                    if (!empty($message['headers']->$imapCol)){
                        $result[$col] = $message['headers']->$imapCol;
                    }
                    break;
            }
        }
        return $result;
    }

    public function getOneImap($atts){
        $mailbox = $this->mailboxesObj->getMailbox($atts['where'][$this->accountIdCol], $atts['where'][$this->mailboxNameCol]);
        $message = $this->mailMessage->get($mailbox['stream'], $atts['cols'], $atts['where']['uid']);

        return $message;
    }
    
    public function getAccountIdChanged($atts){
        $result = $this->mailAccountsObj->getOne(['where' => ['id' => $atts['where']['parentid'], 'cols' => ['name', 'eaddress', 'draftsfolder']]]);
        return ['parentid' => $atts['where']['parentid'], 'mailboxname' => $result['draftsfolder'], 'from' => $this->fromLabel($result)];
    }

    public function getAll ($atts){
        $mailbox = $this->mailboxesObj->getMailbox($atts['where'][$this->accountIdCol], $atts['where'][$this->mailboxNameCol]);
        if ($mailbox){
            if ($atts['cols'] === ['*']){
                $atts['cols'] = $this->allCols;
            }
            $atts['cols'] = array_diff($atts['cols'], ['body']);
            $this->splitRequestedCols($atts['cols'], $this->nonImapCols);
            $nmsgs = $this->mailboxesObj->numMsgs($mailbox);
            if ($nmsgs > 0){
                $imapMsgsOverview = @imap_fetch_overview($mailbox['stream'],"1:{$nmsgs}");
                $values = [];
                foreach ($imapMsgsOverview as $msgOverviewObject){
                    $msgOverviewArray = [];
                    foreach ($this->requestedImapCols as $col){
                        if (isset($msgOverviewObject->$col)){
                            $msgOverviewArray[$col] = $msgOverviewObject->$col;
                        }
                    }
                    $atts['where']['uid'] = $msgOverviewObject->uid;
                    $nonImapValues = $this->getOneNonImap(['where' => $atts['where'], 'cols' => $this->requestedNonImapCols]);
                    $values[] = array_merge($nonImapValues, $this->imapFetchOverviewToTukos($msgOverviewArray));
                }
                $this->foundRows = count($values);
                imap_errors();
                imap_alerts();
                return $values;
            }
        }
        $this->foundRows = 0;
        return [];
    }

    public function duplicateOneExtended($id, $cols){
        $this->setInits();
        $result = array_merge($this->initExtended, $this->getOneExtended(['where' => ['id' => $id], 'cols' => ['parentid','name','from','to','body']]));
        unset($result['id']);
        return $result;
    }

    public function prepareNewValues(&$values, $init=false){
        if (!empty($values['id'])){
            $existingValues = $this->getOne(['where' => $this->idArray($values['id']), 'cols' => ['*']]);
            $values = array_merge($existingValues, $values);
        }else if($init){
            $this->setInits();
            $values = array_merge($this->init, $values);
        }
/*
        if (!empty($values[$this->accountIdCol]['id'])){
             $values[$this->accountIdCol] = $values[$this->accountIdCol]['id'];
        }
*/
    }
    
    public function send ($values, $init=false){
        $this->prepareNewValues($values, $init);
        $accountId = $values[$this->accountIdCol];
        $accountInfo = $this->mailAccountsObj->getAccountInfo(['where' => ['id' => $accountId], 'cols' => ['eaddress', 'username', 'password', 'privacy', 'smtpserverid']]);
        $values['username'] = $accountInfo['username'];
        $values['password'] = $accountInfo['password'];
        $objectsStore = Tfk::$registry->get('objectsStore');
        $smtpModel  = $objectsStore->objectModel('mailsmtps');
        if ($smtpModel->send($accountInfo['smtpserverid'], $values, true)){
            if (!empty($values['uid']) && $values['draft'] == 1){
                $mailbox = $this->mailboxesObj->getMailbox($accountId, $values['mailboxname']);
                $success = @imap_delete($mailbox['stream'], $uidToDelete, FT_UID);
                /* here add creation in 'sent' mailbox if smtp was not user authenticated ?*/ 
                return $success;
            }
            return true;
        }else{
            return false;
        }
    }

    public function saveDraft ($values, $init=false){/*values are in extended form ('parentid')*/
        $this->prepareNewValues($values, $init);
        $accountId = $values[$this->accountIdCol];
        $mailBoxName = $values['mailboxname'];
        if (!$this->mailboxesObj->mailboxExists($accountId, $mailBoxName)){
            Feedback::add('Mailboxdoesnotexists');
            return false;
        }else{
            $mailbox = $this->mailboxesObj->getMailbox($accountId, $mailBoxName);
            $uidToDelete = (isset($values['uid']) && $values['draft'] == 1 ? $values['uid'] : false);
            $drafter = new MailDrafter(); 
            $success = $drafter->save($mailbox, $values);
            if ($success){
                if ($uidToDelete){
                    $success = @imap_delete($mailbox['stream'], $uidToDelete, FT_UID);
                }
                $lastMsgno = $this->mailboxesObj->numMsgs($mailbox);
                $message = $this->mailMessage->get($mailbox['stream'], ['subject', 'uid', 'draft'], "{$lastMsgno}:{$lastMsgno}", 0);
                return [
                    'id' => $this->idString(['parentid' => $accountId, 'mailboxname' => $mailBoxName, 'name' => $message['subject'], 'uid' => $message['uid']]),
                    'draft' => $message['draft'],
                ];
            }else{
                return $success;
            }
        }
    }

    public function delete ($where){
        $where = $this->cleanWhere($where);
        if (!(empty($where['parentid']) || empty($where['mailboxname']) || empty($where['uid']))){
            $mailbox = $this->mailboxesObj->getMailbox($where['parentid'], $where['mailboxname']);
            
            $success = @imap_delete($mailbox['stream'], $where['uid'], FT_UID);
            return $where['uid'];
        }else{
            Feedback::add(['MailDeleteNotAvailableForQuery'  => $query]);
            return false;
        }
    }

    function processOne($valuesToSend){
        return 'To be done';
    }
}
?>
