<?php
/**
 *
 */
namespace TukosLib\Store;

use Aura\Sql\ConnectionFactory;
use TukosLib\Store\QueryBuilder;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Store {
/*
 *
 */
    public function __construct($config){
        $connectionFactory = new ConnectionFactory;
        $this->hook = $connectionFactory->newInstance(
            $config['datastore'], 'host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';charset=utf8mb4', $config['admin'], $config['pass']);
        $this->query = new QueryBuilder($config);
        $this->dbName = $config['dbname'];
        $this->hook->getProfiler()->setActive(true);
    }
    public function query($textQuery, $bind=[]){
        $stmt = $this->hook->query($textQuery, $bind);
        return $stmt;
    }

    public function getProfiles(){
        return $this->hook->getProfiler()->getProfiles();
    }
    
	public function tableList(){
		return isset($this->tableList) ? $this->tableList : $this->tableList = $this->hook->fetchTableList();
	}
	
    public function createTable($tableName, $colsDefinition, $colsIndexes = []){
        $tableDescription = "(" . implode(",", array_map(function($col, $definition){return "`$col` $definition";}, array_keys($colsDefinition), array_values($colsDefinition)));
        if (!empty($colsIndexes)){
            $tableDescription .= ", INDEX(" . implode("), INDEX(", array_map(function($colsIndex){return implode(",", array_map(function($col){return "`$col`";}, $colsIndex));}, $colsIndexes)) . ")";
        }
        $tableDescription .= ") ROW_FORMAT=COMPRESSED ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $stmt = $this->hook->query("CREATE TABLE `$tableName`" . $tableDescription, []);
        if (isset($this->tableList)){
        	$this->tableList[] = $tableName;
        }
    }

    public function tableExists($tableName){
        return in_array($tableName, $this->tableList());
    }

    public function deleteTable($tableName){
        $stmt = $this->hook->query("DROP  TABLE `" . $tableName . "`", []);
        if (isset($this->tableList)){
        	unset($this->tableList[$tableName]);
        }
    }
    
    public function emptyTable($tableName){
        $stmt = $this->hook->query("TRUNCATE  TABLE `" . $tableName . "`", []);
    }        
        
    public function tableStatus(){
        return $this->hook->query('SHOW TABLE STATUS FROM ' . $this->dbName);
    }

    public function tableCols($tableName){
        return $this->hook->query("SHOW COLUMNS FROM `" . $tableName . "`")->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
    public function addCols($colsDescription, $tableName){
        if ($colsDescription){
            $sqlStmt = 'ALTER TABLE `' . $tableName . '`';
            $separator = '';
            foreach ($colsDescription as $col => $description){
                $sqlStmt .= $separator . ' ADD COLUMN `' . $col . '` ' . $description;
                $separator = ',';
            }
            $this->hook->query($sqlStmt);
        }
    }
  
    protected function addMissingColsIfNeeded($colsDescription, $tableName){
        $tableCols = $this->store->tableCols($tableName);
        $colsDefToAdd = [];
        foreach ($colsDescription as $col => $description){
            if (! in_array($col, $tableCols)){
                $colsDefToAdd[$col] = $description;
            }
        }
        $this->addCols($colsDefToAdd, $tableName);
    }

    function setFoundRows($atts, $countResults){
        if (!empty($atts['range']) && $countResults = $atts['range']['limit']){
            $atts['cols'] = ['count(*)'];
            unset($atts['range']);
            unset($atts['orderBy']);
            $query = $this->query->build('newSelect', $atts);
            $result = $this->hook->fetchAll($query, $query->getBindValues());
            $this->foundRows = $result[0]['count(*)'];
        }else{
            $this->foundRows = $countResults;
        }
    }

    function foundRows(){
        return $this->foundRows;
    }
    
    function getAll($atts){
        $query = $this->query->build('newSelect', $atts);
        $result = $this->hook->fetchAll($query, $query->getBindValues());
        $this->setFoundRows($atts, count($result));

        return $result;
    }

    function getAssoc($atts){
        $query = $this->query->build('newSelect', $atts);
        $result = $this->hook->fetchAssoc($query, $query->getBindValues());
        $this->setFoundRows($atts, count($result));
        return $result;
    }

    function getCol($atts){
        $query = $this->query->build('newSelect', $atts);
        $result = $this->hook->fetchCol($query, $query->getBindValues());
        $this->setFoundRows($atts, count($result));
        return $result;
    }

    /*
     * Returns false if object not found, else returns the object as an array
     */
     function getOne($atts){
        $query = $this->query->build('newSelect', $atts);
        return  $this->hook->fetchOne($query, $query->getBindValues());
    }

    function getPairs($atts){
        $query = $this->query->build('newSelect', $atts);
        return $this->hook->fetchPairs($query, $query->getBindValues());
    }

    function getValue($atts){
        if (! isset($atts['cols'])){$atts['cols'] = ['*'];}
        $query = $this->query->build('newSelect', $atts);
        return $this->hook->fetchValue($query, $query->getBindValues());
    }

    function getTableList(){
        return $this->hook->fetchTableList();
    }
    function getTableCols($table){
        return $this->hook->fetchTableCols($table);
    }

    function newSelect(){
        return $this->hook->newSelect();
    }
    
    function newInsert(){
        return $this->hook->newInsert();
    }
    
    function newUpdate(){
        return $this->hook->newUpdate();
    }
    
    function newDelete(){
        return $this->hook->newDelete();
    }
    
    function insert($values, $atts){
        $query = $this->query->build('newInsert', $atts);
        $query->cols($values);
        $stmt = $this->hook->query($query, $query->getBindValues());
        return $stmt->rowCount();
    }
    
    function lastInsertId(){
    	return $this->hook->lastInsertId();
    }

    function update($values, $atts){
        $query = $this->query->build('newUpdate', $atts);
        $query->cols($values);
        $stmt = $this->hook->query($query, $query->getBindValues());
        return $stmt->rowCount();/* if no row found, returns 0, without error message*/
    }
    
    function delete($atts){
        if ($atts['where'] === []){
            return 0;
        }else{
            $query = $this->query->build('newDelete', ['table' => $atts['table'], 'where' => $atts['where']]);
            $stmt = $this->hook->query($query, $query->getBindValues());
            return $stmt->rowCount();
        }
    }
} 
?>
