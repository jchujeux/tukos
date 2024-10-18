<?php
namespace TukosLib\Objects;

use TukosLib\Store\Store;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\Admin\Users\Model as UserModel;

class TukosModel {

    protected $_nextIdTable = 'id';
    protected $optionsTable = 'options';
    protected $tableName = 'tukos';
    protected $_colsDefinition = [
        'id'           =>  'INT(11) PRIMARY KEY',
        'parentid'     =>  "INT(11) NULL DEFAULT NULL",
        'object'       =>  "VARCHAR(80)",
        'name'         =>  "VARCHAR(255) DEFAULT ''",
        'contextid'    =>  'INT(11) NOT NULL',
        'comments'     =>  'longtext',
        'created'      =>  "timestamp",
        'updated'      =>  "timestamp",
        'creator'      =>  'INT(11) NOT NULL',
        'updator'      =>  'INT(11) NOT NULL',
        'permission'   =>  "ENUM ('PL',  'PR', 'RL',  'RO', 'UL', 'PU')",
        'acl'           => 'longtext',
        'grade'          =>  "ENUM ('TEMPLATE', 'NORMAL', 'GOOD', 'BEST')",
        'worksheet'    =>  'longtext',
        'custom'       =>  'longtext',
        'history'      =>  'longtext',
    ];
    protected $_colsIndexes =   [['parentid', 'object'], ['object'], ['updator'], ['contextid']];
        

    public $_textColumns = ['char', 'varc', 'long', 'text'];
    public $_largeColumns = ['mediumte', 'longtext'];
    
    function __construct () {
        Tfk::$registry->set('store', function(){
            return new Store(Tfk::$registry->get('appConfig')->dataSource);
        }); 
    	$this->store  = Tfk::$registry->get('store');
    	$this->colsDescription = $this->_colsDefinition;
        if (!$this->store->tableExists($this->tableName)){
            $now = date('Y-m-d H:i:s');
            $tukosUserId = Tfk::tukosUserId;
            $this->store->createTable($this->tableName, $this->_colsDefinition, $this->_colsIndexes);
            $this->store->insert(['id' => Tfk::tukosSchedulerUserId, 'name' => 'tukosscheduler', 'object' => 'users', 'contextid' => 1, 'permission' => 'RO', 'created' => $now, 'updated' => $now, 'creator' => $tukosUserId, 'updator' => $tukosUserId], ['table' => $this->tableName]);
            $this->store->insert(['id' => Tfk::tukosUserId, 'name' => 'tukos', 'object' => 'users', 'contextid' => 1, 'permission' => 'RO', 'created' => $now, 'updated' => $now, 'creator' => $tukosUserId, 'updator' => $tukosUserId], ['table' => $this->tableName]);
            $this->store->insert(['id' => Tfk::tukosBackOfficeUserId, 'name' => 'tukosBackOffice', 'object' => 'users', 'contextid' => 1, 'permission' => 'RO', 'created' => $now, 'updated' => $now, 'creator' => $tukosUserId, 'updator' => $tukosUserId], ['table' => $this->tableName]);
            require __DIR__.'/Admin/Users/Model.php';
            $this->store->createTable('users', array_merge([ 'id'  =>  'INT(11) PRIMARY KEY'], UserModel::$_colsDefinition), UserModel::$_colsIndexes);
            $this->store->insert(['id' => Tfk::tukosSchedulerUserId, 'rights' => 'SUPERADMIN'], ['table' => 'users']);
            $this->store->insert(['id' => Tfk::tukosUserId, 'rights' => 'SUPERADMIN'], ['table' => 'users']);
            $this->store->insert(['id' => Tfk::tukosBackOfficeUserId, 'rights' => 'RESTRICTEDUSER'], ['table' => 'users']);
            $this->store->insert(['id' => 1, 'name' => 'tukos', 'object' => 'contexts', 'permission' => 'RO', 'created' => $now, 'updated' => $now, 'creator' => $tukosUserId, 'updator' => $tukosUserId], ['table' => $this->tableName]);
            $this->store->createTable($this->_nextIdTable, [/*'id' => 'INT(11)', */'configrange' => 'VARCHAR(20) PRIMARY KEY', 'nextid' => 'INT(11)', 'updated' => 'datetime']);
            forEach (Directory::configStatusRange() as $status => $range){
            	$this->store->insert(['configrange' => $status, 'nextid' => $range, 'updated' => date('Y-m-d H:i:s')], ['table' => $this->_nextIdTable]);
            }
            $this->store->createTable($this->optionsTable, ['name' => 'VARCHAR (80)', 'value' => 'longtext']);
            $this->store->insert(['name' => 'parameters', 'value' => '{"union": false}'], ['table' => $this->optionsTable]);
        }
        $this->textColumns = array_keys(array_filter($this->_colsDefinition, function($def){return in_array(strtolower(substr($def, 0, 4)), $this->_textColumns);}));
        $this->maxSizeCols = ['comments'];
        $this->allCols = array_keys($this->_colsDefinition);
        $this->sharedObjectCols = array_diff($this->allCols, ['object']);
        $this->idColsObjects = ['parentid' => [$this->tableName], 'contextid' => ['contexts'], 'creator' =>['users'], 'updator' => ['users']];
        $this->parameters = json_decode($this->store->getOne(['table' => $this->optionsTable, 'where' => ['name' => 'parameters'], 'cols' => ['value']])['value'], true);
        $this->optionsCache = [];
    }
    
    public function nextId($configStatus, $increment = true){

        $stmt = $this->store->pdo->query('LOCK TABLES ' . $this->_nextIdTable . ' WRITE');
        $nextId =  $this->store->getValue(['table' => $this->_nextIdTable, 'cols' => ['nextid'], 'where' => ['configrange' => $configStatus]]);
        if($increment){ $this->store->update(['nextid' => $nextId+1, 'updated' => date('Y-m-d H:i:s')], ['table' => $this->_nextIdTable, 'where' => ['configrange' => $configStatus]]);}
        $stmt = $this->store->pdo->query('UNLOCK TABLES');
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
    public function getOption($name){
        if ($option = Utl::getItem($name, $this->optionsCache)){
            return $option;
        }else{
            return $this->optionsCache = json_decode($this->store->getOne(['table' => $this->optionsTable, 'where' => ['name' => $name], 'cols' => ['value']])['value'], true);
        }
    }
    
}
?>
