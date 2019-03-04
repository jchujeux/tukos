<?php
namespace TukosLib\Store;

use Aura\SqlQuery\QueryFactory;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

/**
 *
 * Class to build an Aura query object
 *
 */
class QueryBuilder{

    public function __construct($config){
        $this->queryFactory = new QueryFactory($config['datastore'], QueryFactory::COMMON);
    }

    function build($queryMethod, $atts, $bindQuery = null){  //$table, $where=[], $columns = ['*'], $order=[], $range=[], $groupBy=[], $joins=[]){
        $query = $this->queryFactory->$queryMethod();
        $this->bindQuery = empty($bindQuery) ? $query : $bindQuery;// so that bind can be attached to the main query in case of subquery
        foreach ($atts as $method => $arguments){
            $this->$method($query, $arguments);
        }
        return $query;
    }
    
    function bindKey($query, $value){
        static $j = 0;
        $bindKey = 'wvalue' . $j;
        if (isset($value)){
            $this->bindQuery->bindValue($bindKey, $value);
            $j += 1;
        }
        return $bindKey;
    }
    
    function table($query, $table){
        $tableKeyWord = ['Aura\SqlQuery\Common\Select' => 'from', 'Aura\SqlQuery\Common\Update' => 'table', 'Aura\SqlQuery\Common\Insert' => 'into', 'Aura\SqlQuery\Common\Delete' => 'from'];
        $queryObject = get_class($query);
        $queryKeyword = $tableKeyWord[$queryObject];
        $query->$queryKeyword($table);
    }

    function range($query, $range){
        $query->page(0);
        $query->limit($range['limit']);
        $query->offset($range['offset']);
    }
    function set($query, $cols){
        foreach ($cols as $col => $value){
            $query->set($col, $value);
        }
    }
    function cols($query, $cols){
        $query->cols($cols);
    }

    function orderBy($query, $order){
        foreach ($order as $key => $value){// from ['updated' => 'ASC, 'name' => DESC] to [['updated ASC', 'name DESC']]
            if (is_string($key)){
                $order[] = $key . ' ' . $value;
                unset($order[$key]);
            }
        }
        $query->orderBy($order);
    }

   /* $where is an array of the following form:
    *   ['col' => $value, // the 'simple' form, interpreted as col LIKE $value
    *    0 => ['col' =>$col, 'opr' => $opr, 'values' => $values, {'or' => false|true}], // complex form 
    *    1 => [0 => ['col' => $col, ...], // complex form nested (so with parenthesis)
    *          1 => ['col' => ...,     ]
    *         ]
    *    ]
    */
    function where($query, $where){
        foreach ($where as $key => $condition){
            if (is_string($key)){
                $this->simpleWhere($query, $key, $condition);
            }else{//is a complex syntax
                $query->where($this->complexWhere($query, $condition));
            }
        }
    }

    function simpleWhere($query, $col, $value){
        $query->where($col . ' = :' . $this->bindKey($query, $value));
    }
/*    
    function complexWhere($query, $conditions, $leftParen='', $rightParen='', $isOr= false){
        if (is_string($conditions)){// is a plain where condition
            $query->where($conditions);
        }else{
            $isOr = Utl::extractItem('.or', $conditions);
            reset($conditions);
            $firstKey = key($conditions);
            if (is_string($firstKey)){//reached an elementary condition
                $this->complexWhereElement($query, $conditions, $leftParen, $rightParent);

            }else{// is a nested condition
                end($conditions);
                $lastKey = key($conditions);
                foreach ($conditions as $key => $condition){
                    $this->complexWhere($query, $condition, ($key === $firstKey ? '(' : ''), ($key === $lastKey ? ')' : ''), $isOr);
                }
            }
        }
    }
*/    
    function complexWhere($query, $conditions){
        if (is_string($conditions)){
            return $conditions;
        }else{
            reset($conditions);
            if (is_string($firstKey = key($conditions))){
                return $this->complexWhereElement($query, $conditions);
            }else{
                $andOr = Utl::extractItem('.or', $conditions) ? ' OR ' : ' AND ';
                $whereString = '(';
                $prefix = '';
                foreach ($conditions as $key => $condition){
                    $newWhere = $this->complexWhere($query, $condition);
                    if (is_string($newWhere)){
                        $whereString .= $prefix . $newWhere;
                    }else{
                        $whereString .= (is_null($localOr = $newWhere[0]) ? $prefix : ($localOr ? ' OR ' : ' AND ')) . $newWhere[1];
                    }
                    $prefix = $andOr;
                }
                return $whereString . ')';
            }
        }
    }
    
    function complexWhereElement($query, $condition/*, $leftParen, $rightParen*/){
        $col = Utl::getItem('col', $condition, '');
        $opr = $condition['opr'];
        $values = Utl::getItem('values', $condition);
        switch ($opr){
            case 'IN SELECT':
            case 'NOT IN SELECT':
            case 'EXISTS':
            case 'NOT EXISTS': 
                $whereOpr = ['IN SELECT' => ' IN (', 'NOT IN SELECT' => ' NOT IN (', 'EXISTS' => 'EXISTS (', 'NOT EXISTS' => 'NOT EXISTS ('];
            	$whereString = $col . $whereOpr[$opr] . (is_array($values) ? $this->build('newSelect', $values, $query)->__toString() : $values) . ')';
                break;
            case 'IS NULL':
            case 'IS NOT NULL':
                $whereString = $col . ' ' . $opr;
                break;
            case 'BETWEEN':
            case 'NOT BETWEEN':
                $whereString = $col . ' ' . $opr . ' :' . $this->bindKey($query, $values[0]) . ' AND :' . $this->bindKey($query, $values[1]);
                break;
            default:
                if (is_array($values)){
                    $whereString = $col . ' ' . $opr . ' (:' . $this->bindKey($query, $values) . ')';
                }else{
                    $whereString = $col . ' ' . $opr . ' :' . $this->bindKey($query, $values);
                }
                break;
        }
        //$andorwhere = ((isset($condition['or']) && $condition['or'] === true) ? 'orWhere' : 'where');
        //$query->$andorwhere($leftParen . $whereString . $rightParen);
        if ($or = Utl::getItem('or', $condition) === null){
            return $whereString;
        }else{
            return [Utl::getItem('or', $condition), $whereString];
        }
    }
    
    function groupBy($query, $groupBy){
        $query->groupBy($groupBy);
    }
    
    function join($query, $joins){
        if (is_array($joins[0])){
            foreach ($joins as $join){
                $query->join($join[0], $join[1], $join[2]);
            }
        }else{
            $query->join($joins[0], $joins[1], $joins[2]);
        }
        return $query;
    }
    
    function distinct($query, $trueOrFalse){
        $query->distinct($trueOrFalse);
    }

    function limit($query, $nbOfRows){
        $query->limit($nbOfRows);
    }
}
?>