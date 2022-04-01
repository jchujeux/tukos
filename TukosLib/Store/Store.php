<?php
namespace TukosLib\Store;

use Aura\Sql\ExtendedPdo;
use Aura\SqlSchema\ColumnFactory;
use Aura\SqlSchema\MysqlSchema;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Store {
    public function __construct($config){
        $this->pdo = new ExtendedPdo(
            $config['datastore'] . ':host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';charset=utf8mb4', $config['admin'], $config['pass']);
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        $this->pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, true);
        $this->query = new QueryBuilder($config);
        $this->dbName = $config['dbname'];
        $this->pdo->getProfiler()->setActive(true);
        $this->pdo->getProfiler()->setLogFormat("{duration} </td><td>{statement}</td><td>{backtrace}");
    }
    public function query($textQuery, $bind=[]){
        $stmt = $this->pdo->query($textQuery, $bind);
        return $stmt;
    }
    public function profilerMessages(){
        return $this->pdo->getProfiler()->getLogger()->getMessages();
    }
    public function getSchema(){
        return isset($this->chema) ? $this->schema : new MysqlSchema($this->pdo, new ColumnFactory());
    }
	public function tableList(){
		return isset($this->tableList) ? $this->tableList : $this->tableList = $this->getSchema()->fetchTableList();
	}
    public function createTable($tableName, $colsDefinition, $colsIndexes = []){
        $tableDescription = "(" . implode(",", array_map(function($col, $definition){return "`$col` $definition";}, array_keys($colsDefinition), array_values($colsDefinition)));
        if (!empty($colsIndexes)){
            $tableDescription .= ", INDEX(" . implode("), INDEX(", array_map(function($colsIndex){return implode(",", array_map(function($col){return "`$col`";}, $colsIndex));}, $colsIndexes)) . ")";
        }
        $tableDescription .= ") ROW_FORMAT=COMPRESSED ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->pdo->query("CREATE TABLE `$tableName`" . $tableDescription, []);
        if (isset($this->tableList)){
        	$this->tableList[] = $tableName;
        }
    }
    public function tableExists($tableName){
        return in_array($tableName, $this->tableList());
    }
    public function deleteTable($tableName){
        $this->pdo->query("DROP  TABLE `" . $tableName . "`", []);
        if (isset($this->tableList)){
        	unset($this->tableList[$tableName]);
        }
    }
    public function emptyTable($tableName){
        $this->pdo->query("TRUNCATE  TABLE `" . $tableName . "`", []);
    }        
        
    public function tableStatus(){
        return $this->pdo->query('SHOW TABLE STATUS FROM ' . $this->dbName);
    }

    public function tableCols($tableName){
        return $this->pdo->query("SHOW COLUMNS FROM `" . $tableName . "`")->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
    public function tableColsStructure($tableName){
        return $this->pdo->query("SHOW COLUMNS FROM `" . $tableName . "`")->fetchAll(\PDO::FETCH_ASSOC);
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
            $this->pdo->query($sqlStmt);
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
            $result = $this->pdo->fetchAll((string) $query, $query->getBindValues());
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
        $result = $this->pdo->fetchAll((string) $query, $query->getBindValues());
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
        $result = $this->pdo->fetchAssoc((string) $query, $query->getBindValues());
        $this->setFoundRows($atts, count($result));
        return $result;
    }
    function getCol($atts){
        $query = $this->query->build('newSelect', $atts);
        $result = $this->pdo->fetchCol((string) $query, $query->getBindValues());
        $this->setFoundRows($atts, count($result));
        return $result;
    }
    /*
     * Returns false if object not found, else returns the object as an array
     */
     function getOne($atts){
        $union = Utl::getItem('union', $atts);
         $query = $this->query->build('newSelect', $atts);
        $result = $this->pdo->fetchAll((string) $query, $query->getBindValues());
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
    }
    function getPairs($atts){
        $query = $this->query->build('newSelect', $atts);
        return $this->pdo->fetchPairs((string) $query, $query->getBindValues());
    }
    function getValue($atts){
        if (! isset($atts['cols'])){$atts['cols'] = ['*'];}
        $query = $this->query->build('newSelect', $atts);
        return $this->pdo->fetchValue((string) $query, $query->getBindValues());
    }
    function newSelect(){
        return $this->pdo->newSelect();
    }
    function newInsert(){
        return $this->pdo->newInsert();
    }
    function newUpdate(){
        return $this->pdo->newUpdate();
    }
    function newDelete(){
        return $this->pdo->newDelete();
    }
    function insert($values, $atts){
        $query = $this->query->build('newInsert', $atts);
        $query->cols($values);
        //$stmt = $this->pdo->query((string) $query, $query->getBindValues());
        $stmt = $this->pdo->perform((string) $query, $query->getBindValues());
        /*$bind = $query->getBindValues();
        $stmt = $this->pdo->prepare((string) $query, $bind);
        $stmt->execute($bind);*/
        return $stmt->rowCount();
    }
    function lastInsertId(){
    	return $this->pdo->lastInsertId();
    }
    function update($values, $atts){
        $query = $this->query->build('newUpdate', $atts);
        $query->cols($values);
        //$stmt = $this->pdo->query((string) $query, $query->getBindValues());
        $stmt = $this->pdo->perform((string) $query, $query->getBindValues());
        /*$bind = $query->getBindValues();
        $stmt = $this->pdo->prepare((string) $query, $bind);
        $stmt->execute($bind);*/
        return $stmt->rowCount();/* if no row found, returns 0, without error message*/
    }
    function delete($atts){
        if ($atts['where'] === []){
            return 0;
        }else{
            $query = $this->query->build('newDelete', ['table' => $atts['table'], 'where' => $atts['where']]);
            //$stmt = $this->pdo->query((string) $query, $query->getBindValues());
            $stmt = $this->pdo->perform((string) $query, $query->getBindValues());
            /*$bind = $query->getBindValues();
            $stmt = $this->pdo->prepare((string) $query, $bind);
            $stmt->execute($bind);*/
            return $stmt->rowCount();
        }
    }
} 
?>
