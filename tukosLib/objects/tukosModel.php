<?php
namespace TukosLib\Objects;

use TukosLib\Store\Store;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class TukosModel {

    protected $_nextIdTable = 'id';
    protected $tableName = 'tukos';
    protected $_colsDefinition = [
        'id'           =>  'INT(11) NOT NULL',
        'parentid'     =>  "INT(11) NOT NULL DEFAULT '0'",
        'object'       =>  "VARCHAR(80)",
        'name'         =>  "VARCHAR(255) DEFAULT ''",
        'contextid'    =>  'INT(11) NOT NULL',
        'comments'     =>  'longtext',
        'created'      =>  "timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'",
        'updated'      =>  "timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'",
        'creator'      =>  'INT(11) NOT NULL',
        'updator'      =>  'INT(11) NOT NULL',
        'permission'   =>  "ENUM ('NOTDEFINED', 'PR', 'RO', 'PU', 'ACL')",
        'grade'          =>  "ENUM ('TEMPLATE', 'NORMAL', 'GOOD', 'BEST')",
        'worksheet'    =>  'longtext',
        'custom'       =>  'longtext',
        'history'      =>  'longtext',
    ];

    protected $_keysDefinition ="PRIMARY KEY (`ID`), KEY (`parentid`), KEY(`object`), KEY (`contextid`), KEY (`creator`), KEY (`updator`), KEY (`permission`), KEY (`grade`)";
    public $_textColumns = ['char', 'varc', 'long', 'text'];
    public $_largeColumns = ['medi', 'long'];
    
    function __construct () {
        Tfk::$registry->set('store', function(){
            return new Store(Tfk::$registry->get('appConfig')->dataSource);
        }); 
    	$this->store  = Tfk::$registry->get('store');
        if (!$this->store->tableExists($this->tableName)){
            $this->store->createTable($this->tableName, $this->_colsDefinition, $this->_keysDefinition);
            $this->store->insert(['id' => 0, 'name' => 'tukos', 'object' => 'users', 'contextid' => 1], ['table' => $this->tableName]);
            $this->store->insert(['id' => 1, 'name' => 'tukos', 'object' => 'contexts'], ['table' => $this->tableName]);
            $this->store->createTable($this->_nextIdTable, ['id' => 'INT(11)', 'configrange' => 'VARCHAR(20)', 'nextid' => 'INT(11)', 'updated' => 'datetime'], 'PRIMARY KEY (`configrange`)');
            forEach (Directory::configStatusRange() as $status => $range){
            	$this->store->insert(['configrange' => $status, 'nextid' => $range, 'updated' => date('Y-m-d H:i:s')], ['table' => $this->_nextIdTable]);
            }
        }
        $this->textColumns = array_keys(array_filter($this->_colsDefinition, function($def){return in_array(strtolower(substr($def, 0, 4)), $this->_textColumns);}));
        //$this->maxSizeCols = array_keys(array_filter($this->_colsDefinition, function($def){return in_array(strtolower(substr($def, 0, 4)), $this->_largeColumns);}));
        $this->maxSizeCols = ['comments'];
        $this->allCols = array_keys($this->_colsDefinition);
        $this->sharedObjectCols = array_diff($this->allCols, ['object']);
        $this->idColsObjects = ['parentid' => [$this->tableName], 'contextid' => ['contexts'], 'creator' =>['users'], 'updator' => ['users']];
        $this->idCols = array_keys($this->idColsObjects);
    }
    
    public function nextId($configStatus, $increment = true){

        $stmt = $this->store->hook->query('LOCK TABLES ' . $this->_nextIdTable . ' WRITE');
        $nextId =  $this->store->getValue(['table' => $this->_nextIdTable, 'cols' => ['nextid'], 'where' => ['configrange' => $configStatus]]);
        if($increment){ $this->store->update(['nextid' => $nextId+1, 'updated' => date('Y-m-d H:i:s')], ['table' => $this->_nextIdTable, 'where' => ['configrange' => $configStatus]]);}
        $stmt = $this->store->hook->query('UNLOCK TABLES');
    	return $nextId;
    }

    public function tableName(){
        return $this->tableName;
    }
    
    public function getOne($atts){
        $atts['table'] = $this->tableName;
        $atts['where'] = SUtl::deletedFilter($atts['where']);
        return $this->store->getOne($atts);
    }
    public function getAll($atts){
        $atts['table'] = $this->tableName;
        $atts['where'] = SUtl::deletedFilter($atts['where']);
        return $this->store->getAll($atts);
    }
    
}
?>
