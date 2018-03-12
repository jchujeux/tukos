<?php
/*
 *
 * class for the mail account tukos object
 */
namespace TukosLib\Objects\Admin\Mail;

use TukosLib\Objects\ObjectTranslator;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class AbstractModel extends ObjectTranslator {

    protected $gradeOptions = ['TEMPLATE', 'NORMAL', 'GOOD', 'BEST'];
    protected $configStatusOptions = ['tukos', 'bustrack', 'wine', 'itm', 'sports', 'physio', 'users'];
    protected $permissionOptions = ['NOTDEFINED', 'PR', 'RO', 'PU', 'ACL'];

    public static function translationSets(){
        return [];
    }

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator);
        $this->objectName = $objectName;
        $this->user  = Tfk::$registry->get('user');
        $this->idColsObjects = [$this->accountIdCol => ['mailaccounts']];
        $this->idCols = array_keys($this->idColsObjects);

        //$this->tableName = '';
        $this->allCols = [];
        $this->nonImapCols = [];
        $this->idProps    = [];
        
        $this->extendedNameCols = ['name'];
        $this->colsToTranslate = ['permission', 'grade', 'configstatus'];
        
    }
    
    public function setInits(){
        $this->init = [];
    }

    public function initialize($init=[]){
        $this->setInits();
        return array_merge ($this->init, $init) ;
    }

    public function initializeExtended($init=[]){
        return $this->initialize($init);
    }
    public function getItemCustomization($where, $keys){
        return [];
    }
    public function getCombinedCustomization($where, $view, $keys){
        return [];
    }

    public function extractExtendedName(&$extendedNameValues){
        $extendedNameValues['name'] = Utl::concat(Utl::extractItems($this->extendedNameCols, $extendedNameValues));
    }

    function splitRequestedCols($requestedCols, $nonImapCols){
        $this->requestedNonImapCols = ['id'];/* 'id' should always be there even if not requested*/
        $this->requestedImapCols = [];
        if ($requestedCols === ['*']){
            $requestedCols = $this->allCols;
        }
        foreach ($requestedCols as $col){
            if ($col !== 'id'){
                $key = array_search($col, $nonImapCols);
                if (!($key === false)){
                    $this->requestedNonImapCols[] = $col;
                }else{
                    $this->requestedImapCols[]    = $col;
                }
            }
        }
    }
    
    function options($property){
        $name = $property . 'Options';
        return $this->$name;
    }

    public function idArray($idString){
        return array_combine($this->idProps, explode(',', substr($idString, 1, -1)));
    }
    
    public function idString($idArray){
        return '[' . implode(',', array_intersect_key($idArray, array_flip($this->idProps))) . ']';
    }
    
    function getOneNonImap($atts){
        if (empty($atts['where']['id'])){
            $atts['where']['id'] = $this->idString($atts['where']);
        }
        $result = [];
        foreach ($atts['cols'] as $col){
            $result[$col] = $atts['where'][$col];
        }
        return $result;    
    }
    
    function cleanWhere($where){
        if (isset($where['id'])){
            return array_merge($where, $this->idArray($where['id']));
        }else{
            return $where;
        }
    }

    public function setIdCols($objectIdCols){
    }

    public function _getOne ($atts){
        $imapValues    = ($this->requestedImapCols ? $this->getOneImap(['where' => $atts['where'], 'cols' => $this->requestedImapCols]) : []);
        $nonImapValues = $this->getOneNonImap(['where' => $atts['where'], 'cols' => $this->requestedNonImapCols]);
        return array_merge($nonImapValues, $imapValues);
    }

    public function getOne ($atts){
        $atts['where'] = $this->cleanWhere($atts['where']);
        $this->splitRequestedCols($atts['cols'], $this->nonImapCols);
        return $this->_getOne($atts);
    }

    public function getOneExtended($atts){
        return $this->getOne($atts);
    }
    
    public function getAllExtended($atts){
        return $this->getAll($atts);
    }

    public function foundRows(){
        return $this->foundRows;/* to be set by $this->getAll*/
    }
    
    public function insert ($values, $init=false){
        Feedback::add('MailInsertNotImplemented');
        return false;
    }
    public function insertExtended ($values, $init=false){
        Feedback::add('MailInsertExtendedNotImplemented');
        return false;
    }
    
    public function updateOne ($values, $where=[]){
        Feedback::add('MailUpdateNotImplemented');
        return false;
    }
    public function updateOneExtended ($values, $where=[]){
        Feedback::add('MailUpdateNotImplemented');
        return false;
    }
    public function updateAll ($values, $where=[]){
        Feedback::add('MailUpdateNotImplemented');
        return false;
    }
    public function duplicate($ids, $cols=['*']){
        Feedback::add('MailDuplicateExtendedNotImplemented');
        return false;
    }
            
    
    public function delete ($where){
        Feedback::add('MailDeleteExtendedNotImplemented');
        return false;
    }
    
    public function summary($activeUserFilters){
        Feedback::add('MailSummaryNotImplemented');
        return false;
    }
}
?>
