<?php
/**
 *
 * Provide methods to deal with parent - child methods for object items
 */
namespace TukosLib\Objects;

use TukosLib\Utils\Utilities as Utl;

use TukosLib\TukosFramework as Tfk;

trait Query {
    function prefixTable($col){
        if (isset($this->prefixTableCache[$col])){
            return $this->prefixTableCache[$col];
        }else{
            $noPrefixCharacters = str_split('*(.');
            foreach ($noPrefixCharacters as $character){
                if (strpos($col, $character)){
                    $this->prefixTableCache[$col] = '';
                    return $this->prefixTableCache[$col];
                }
            }
            if (in_array($col, $this->tukosModel->allCols)){
                $this->hasTukosTableCols = true;
                $this->prefixTableCache[$col] = $this->tukosModel->tableName() . '.';
                return $this->prefixTableCache[$col];
            }else{
                $this->hasObjectTableCols = true;
                $this->prefixTableCache[$col] = $this->tableName . '.';
                return $this->prefixTableCache[$col];
            }
        }
    }

    private function transform($queryAtts){
        $this->hasTukosTableCols  = false;
        $this->hasObjectTableCols = false;
        $this->prefixTableCache = [];
        if (!empty($queryAtts['orderBy'])){
            $queryAtts['orderBy'] = $this->transformOrderBy($queryAtts['orderBy']);
        }
        $queryAtts['cols'] = $this->transformCols($queryAtts['cols']);
        $queryAtts['where'][] = $this->deleteFilter();        
        $queryAtts['where'] = $this->transformWhere($queryAtts['where']);
        if (!empty($queryAtts['groupBy'])){
            $queryAtts['groupBy'] = $this->transformCols($queryAtts['groupBy']);
        }
        if (!$this->hasTukosTableCols){
            return $queryAtts;
        }
        $tukosTableName = $this->tukosModel->tableName();
        if ($this->hasObjectTableCols){
            $queryAtts['join'][] = ['inner', $tukosTableName, $tukosTableName . '.id = ' . $this->tableName . '.id'];
        }else{
            $queryAtts['table'] = $tukosTableName;
            $queryAtts['where'][$tukosTableName . '.table'] = $this->tableName;
        }
        return $queryAtts;
    }
    
    private function transformOrderBy($orderBy){
        $transformedOrderBy = [];
        foreach ($orderBy as $col => $direction){
            $transformedOrderBy[] = $this->prefixTable($col) . ' ' . $direction;
        }
        return $transformedOrderBy;
    }

    private function transformCols($cols){
        $transformedCols = [];
        foreach ($cols as $col){
            $transformedCols[] = $this->prefixTable($col) . $col;
        }
        return $transformedCols;
    }

    private function transformWhere($where){
        $transformedWhere = [];
        foreach ($where as $key => $condition){
            if (is_string($key)){// is an elementary condition
                if (is_array($condition)){// transform from [$col => [$opr, $value]] into ['col' => $col, 'opr' => $opr, 'values' => $values]
                    if (isset($this->textColumns) && in_array($key, $this->textColumns)){
                        $transformedWhere[] = ['col' => $this->prefixTable($key) . $key, 'opr' => 'COLLATE UTF8_GENERAL_CI ' . $condition[0], 'values' => $condition[1]];// so that case insensitive
                    }else{
                        $transformedWhere[] = ['col' => $this->prefixTable($key) . $key, 'opr' =>                              $condition[0], 'values' => $condition[1]];
                    }            
                    
                }else if ($where[$key] !== '%'){// is a simple where
                    $transformedWhere[$this->prefixTable($key) . $key] = $condition;
                }
            }else{// is a complex elementary condition or a nested condition
                reset($condition);
                if (is_string(key($condition))){// is a complex elementary condtion
                    $condition['col'] = $this->prefixTable($condition['col']) . $condition['col'];
                    $transformedWhere[] = $condition;
                }else{//is a nested condition
                    $transformedWhere[] = $this->transformWhere($condition);
                }
            }
        }
        return $transformedWhere;
    }

    function deleteFilter(){
        return  ['col' => 'id', 'opr' => '>', 'values' => 0];
    }


}
?>
