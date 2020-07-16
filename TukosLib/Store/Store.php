<?php
namespace TukosLib\Store;

use Aura\Sql\ConnectionFactory;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Store {
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
        $this->hook->query("CREATE TABLE `$tableName`" . $tableDescription, []);
        if (isset($this->tableList)){
        	$this->tableList[] = $tableName;
        }
    }
    public function tableExists($tableName){
        return in_array($tableName, $this->tableList());
    }
    public function deleteTable($tableName){
        $this->hook->query("DROP  TABLE `" . $tableName . "`", []);
        if (isset($this->tableList)){
        	unset($this->tableList[$tableName]);
        }
    }
    public function emptyTable($tableName){
        $this->hook->query("TRUNCATE  TABLE `" . $tableName . "`", []);
    }        
        
    public function tableStatus(){
        return $this->hook->query('SHOW TABLE STATUS FROM ' . $this->dbName);
    }

    public function tableCols($tableName){
        return $this->hook->query("SHOW COLUMNS FROM `" . $tableName . "`")->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
    public function tableColsStructure($tableName){
        return $this->hook->query("SHOW COLUMNS FROM `" . $tableName . "`")->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function addCols($colsDescription, $tableName){
        if (!empty($colsDescription)){
            $alterOptions = '';
            $separator = '';
            foreach ($colsDescription as $col => $description){
                $alterOptions .= $separator . ' ADD COLUMN `' . $col . '` ' . $description;
                $separator = ',';
            }
            $sqlStmt = "ALTER TABLE `$tableName` $alterOptions";
            Feedback::add(Tfk::tr('addingcolumn(s)') . ': ' . $sqlStmt);
            $this->hook->query($sqlStmt);
        }
    }
    public function addMissingColsIfNeeded($colsDescription, $tableName){
        $this->addCols(array_diff_key($colsDescription, Utl::toAssociative($this->tableColsStructure($tableName), 'Field')), $tableName);
        if (($configStore = Tfk::$registry->get('configStore'))->dbName !== $this->dbName && $configStore->tableExists($tableName)){
            $configStore->addMissingColsIfNeeded($colsDescription, $tableName);
        }
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
        $union = Utl::getItem('union', $atts);
        $query = $this->query->build('newSelect', $atts);
        $result = $this->hook->fetchAll($query, $query->getBindValues());
        $this->setFoundRows($atts, count($result));
        if ($union === 'merge'){
            if (count($result) > 1 && isset($result[0]['id'])){
                $mergeLhsKey = []; $mergeRhsKey = [];
                foreach($result as $key => &$item){
                    $userOrConfig = Utl::extractItem('database', $item);
                    if (($id = $item['id']) < 10000){
                        if ($userOrConfig === 'user'){
                            $mergeRhsKey[$id] = $key;
                        }else{
                            $mergeLhsKey[$id] = $key;
                        }
                    }
                }
                unset($item);
                foreach($mergeRhsKey as $id => $rhsKey){
                    if (($lhsKey = Utl::getItem($id, $mergeLhsKey,false)) !== false){
                        $result[$lhsKey] = array_merge($result[$lhsKey], array_filter($result[$rhsKey]));
                        unset($result[$rhsKey]);
                    }
                }
                $result = array_values($result);
            }else{
                foreach($result as &$item){
                    unset($item['database']);
                }
            }
        }
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
        $union = Utl::getItem('union', $atts);
         $query = $this->query->build('newSelect', $atts);
        $result = $this->hook->fetchAll($query, $query->getBindValues());
        if ($union === 'merge' && count($result) > 1){
            $db0 = Utl::extractItem('database', $result[0]);
            if ($db0){
                $db1 = Utl::extractItem('database', $result[1]);
                if ($db0 !== $db1){
                    return $db0 = 'user' ? array_merge($result[1], array_filter($result[0])) : array_merge($result[0], array_filter($result[1]));
                }
            }
        }
        return empty($result) ? false : $result[0];
        //return  $this->hook->fetchOne($query, $query->getBindValues());
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
