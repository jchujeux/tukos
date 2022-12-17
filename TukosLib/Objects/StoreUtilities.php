<?php
namespace TukosLib\Objects;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class StoreUtilities {

    public static $tukosModel = null;
    public static $tukosTableName = null;
    public static $store = null;
    public static $objectsStore = null;
    public static $hasObjectCols = false;
    public static $hasTukosCols = false;
    public static $hasSubSelect = false;
    private static $idColsCache = [];
    private static $extendedNamesCache = [];
    private static $extendedNamesObjectCache = [];
    
    public static function instantiate(){
        if (is_null(self::$tukosModel)){
            self::$tukosModel = Tfk::$registry->get('tukosModel');
            self::$tukosTableName = self::$tukosModel->tableName();
            self::$store = self::$tukosModel->store;
            self::$objectsStore = Tfk::$registry->get('objectsStore');
        } 
    }
    public static function objectTranslatedExtendedNames($model, $storeAtts = []){
    	$requestedCols = Utl::getItem('cols', $storeAtts, []);
    	$storeAtts['cols'] = array_unique(array_merge(Utl::getItem('cols', $storeAtts, ['id']), $model->extendedNameCols));
    	$values = Utl::toAssociative($model->translateAll($model->getAllExtended($storeAtts)), 'id');
    	if (empty($values)){
    		return [];
    	}else{
	    	$extendedNameIdCols = array_intersect($model->aliasExtendedNameCols, $model->idCols);
	    	if (!empty($extendedNameIdCols)){
	    		$extraIds = $extraExtendedNames = [];
	    		foreach($extendedNameIdCols as $col){
	    			$extraIds = array_filter(array_unique(array_merge($extraIds, array_unique(array_column($values, $col)))));
	    		}
	    		if(!empty($extraIds)){
	    			$extraExtendedNames = self::translatedExtendedNames($extraIds);
	    		}
	    	}
			$result = [];
			$keepName = in_array('name', $requestedCols) && !$model->extendedNameCols !== ['name'];
			$colsToRemove = array_diff($model->aliasExtendedNameCols, $requestedCols);
			foreach ($values as $id => $value){
			    if ($keepName){
			        $value['sname'] = $value['name'];
			    }
			    $extendedNameValues = Utl::getItems($model->aliasExtendedNameCols, $value);
			    $presentExtendedNameIdCols = array_intersect($extendedNameIdCols, array_keys(array_filter($extendedNameValues)));
		        foreach ($presentExtendedNameIdCols as $col){
					$colId = $extendedNameValues[$col];
		        	$extendedNameValues[$col] = Utl::getItem($colId, $extraExtendedNames, $colId);
		        }	
		        Utl::extractItems($colsToRemove, $value);
		        $result[$id] = array_merge($value, ['name' => (self::$extendedNamesCache[$id] = implode(' ', $extendedNameValues))]);
		        self::$extendedNamesObjectCache[$id] = $model->objectName;
			}
			return $result;
    	}
    }
    public static function translatedExtendedNames($ids){
        $extendedNames = [];
        foreach($ids as $id){
            if ($extendedName = Utl::getItem($id, self::$extendedNamesCache)){
                $extendedNames[$id] = $extendedName;
            }else{
                $idsToProcess[] = $id;
            }
        }
        if (isset($idsToProcess)){
            $objectNamesAndIds = Utl::toAssociativeGrouped(self::$tukosModel->getAll(['where' => [['col' => 'id', 'opr' => 'IN', 'values' => $idsToProcess]], 'cols' => ['id', 'object'], 'union' => self::$tukosModel->parameters['union']]), 'object', 'true');
            $extraIds = $values = [];
            foreach ($objectNamesAndIds as $objectName => $objectIds){
                $model = self::$objectsStore->objectModel($objectName);
                $values[$objectName] = Utl::toAssociative($model->translateAll($model->getAll(['where' => [['col' => 'id', 'opr' => 'IN', 'values' => $objectIds]], 'cols' => array_merge(['id'], $model->extendedNameCols)])), 'id');
                $extendedNameIdCols = array_intersect($model->aliasExtendedNameCols, $model->idCols);
                if (!empty($extendedNameIdCols)){
                    $extendedNameIdColsByObject[$objectName] = $extendedNameIdCols;
                    foreach($extendedNameIdCols as $col){
                        $extraIds = array_filter(array_unique(array_merge($extraIds, array_unique(array_column($values[$objectName], $col)))));
                    }
                }else{
                    $extendedNameIdColsByObject[$objectName] = [];
                }
            }
            $missingIds = array_diff($extraIds, $idsToProcess);
            if (!empty($missingIds)){
                self::translatedExtendedNames($missingIds);
            }
            foreach ($values as $objectName => $idsExtendedNameCols){
                foreach ($idsExtendedNameCols as $id => $extendedNameCols){
                    if (empty($extendedNameIdColsByObject[$objectName])){
                        $extendedNames[$id] = self::$extendedNamesCache[$id] = implode(' ', $extendedNameCols);
                        self::$extendedNamesObjectCache[$id] = $objectName;
                    }
                }
            }
            foreach ($values as $objectName => $idsExtendedNameCols){
                foreach ($idsExtendedNameCols as $id => $extendedNameCols){
                    if (!empty($extendedNameIdColsByObject[$objectName])){
                        foreach($extendedNameIdColsByObject[$objectName] as $col){
                            $colId = $extendedNameCols[$col];
                            $extendedNameCols[$col] = Utl::getItem($colId, self::$extendedNamesCache, $colId);
                        }
                        $extendedNames[$id] = self::$extendedNamesCache[$id] = implode(' ', $extendedNameCols);
                        self::$extendedNamesObjectCache[$id] = $objectName;
                    }
                }
            }
        }
        return $extendedNames;
    }
    public static function addItemIdCols($item, $idCols){
        self::$idColsCache = array_unique(array_merge(self::$idColsCache, array_filter(array_values(Utl::getItems(array_intersect(array_keys($item), $idCols), $item)))));
    }
    
    public static function addItemsIdCols($items, $idCols){
        $ids = [];
        foreach ($idCols as $idCol){
            $ids = array_filter(array_unique(array_merge($ids, array_unique(array_column($items, $idCol)))));
        }
        self::$idColsCache = array_unique(array_merge(self::$idColsCache, $ids));
    }
    public static function addIdCol($id){
        if (!in_array($id, self::$idColsCache)){
            self::$idColsCache[] = $id;
        }
    }
    public static function addIdCols($idCols){
        self::$idColsCache = array_unique(array_merge(self::$idColsCache, $idCols));
    }
    public static function resetIdColsCache(){
        self::$idColsCache = [];
    }
    private static function translatedExtendedIds($ids){
        if (empty($ids)){
            return [];
        }else{
            $result = [];
            $values = self::translatedExtendedNames($ids);
            foreach($values as $id => $extendedName){
                $result[$id] = ['name' => $extendedName, 'object' => self::$extendedNamesObjectCache[$id]];
            }
            return $result;
        }
    }
    public static function translatedExtendedIdCols($emptyIdColsCache = true){
    	$extendedIdCols =  self::translatedExtendedIds(self::$idColsCache);
    	if ($emptyIdColsCache){
    		self::$idColsCache = [];
    	}
    	return $extendedIdCols;
    }
    
    public static function resetCols(){
        self::$hasObjectCols = false;
        self::$hasTukosCols = false;
    }
    
    public static function transformGet($queryAtts, $objectName, $maxSizeCols = [], $fieldMaxSize = 0, $asCol = true){
        $eliminated = Utl::extractItem('eliminateditems', $queryAtts);
        if ($union = Utl::getItem('union', $queryAtts)){
            self::$hasSubSelect = false;
            $cols = $queryAtts['cols'];
        }
        $queryAtts['where'] = self::transformWhere(self::deletedFilter($queryAtts['where'], $eliminated), $objectName);
        if (self::$hasSubSelect && $union){
            $queryAtts['union'] = $union = false;
        }
        $queryAtts['cols'] = self::transformCols($queryAtts['cols'], $objectName, $maxSizeCols, $fieldMaxSize, $asCol);
        if (!empty($queryAtts['orderBy'])){
            if ($union){
                if (array_intersect($orderByKeys = array_keys($queryAtts['orderBy']), $cols) !== $orderByKeys){
                    $queryAtts['union'] = $union = false;
                }
            }
            $queryAtts['orderBy'] = self::transformOrderBy($queryAtts['orderBy'], $objectName, $union);
        }
        if (!empty($queryAtts['groupBy'])){
            $queryAtts['groupBy'] = self::transformCols($queryAtts['groupBy'], $objectName);
        }
        if (!self::$hasTukosCols){
            self::resetCols();
            return $queryAtts;
        }
        if (self::$hasObjectCols){
            if (!isset($queryAtts['join'])){
                $queryAtts['join'] = [];
            }
            array_unshift($queryAtts['join'],  ['inner', self::$tukosTableName, self::$tukosTableName . '.id = ' . ($eliminated ? '-' : '') . $objectName . '.id']);
        }else{
            $queryAtts['table'] = self::$tukosTableName;
        }
        if ($objectName !== self::$tukosTableName){
        	$queryAtts['where'][self::$tukosTableName . '.object'] = $objectName;
        }
        self::resetCols();
        return $queryAtts;
    }
 
     public static function transformUpdate(&$item, $queryAtts, $objectName){
        //self::resetCols();
        $queryAtts['where'] = self::transformWhere($queryAtts['where'], $objectName);
        if (!empty($item)){
            $item = self::transformItem($item, $objectName, $queryAtts);
        }
        if (!self::$hasTukosCols){
            self::resetCols();
            return $queryAtts;
        }
        if (self::$hasObjectCols){
            $queryAtts['join'][] = ['inner', self::$tukosTableName, self::$tukosTableName . '.id = ' . $objectName . '.id'];
        }else{
            $queryAtts['table'] = self::$tukosTableName;
        }
        $queryAtts['where'][self::$tukosTableName . '.object'] = $objectName;
        self::resetCols();
        return $queryAtts;
    }
  
    public static function deletedFilter($where, $eliminated = false){
    	$where[] = ['col' => 'id', 'opr' => $eliminated ? '<' : '>', 'values' => 0];
    	return $where;
    }

    public static function colsPrefix($col, $objectName){
        if (strlen($col) !== strcspn($col, '.(*')){
            return '';
        }else if (in_array($col, self::$tukosModel->allCols)){
             self::$hasTukosCols = true;
             return self::$tukosTableName . '.';
        }else{
            self::$hasObjectCols = true;
            return $objectName . '.';
        }
    }

    public static  function transformOrderBy($orderBy, $objectName, $union){
        if ($union){
            return $orderBy;
        }else{
           $transformedOrderBy = [];
            foreach ($orderBy as $col => $direction){
                $transformedOrderBy[] = self::colsPrefix($col, $objectName) . $col . ' ' . $direction;
            }
            return $transformedOrderBy;
        }
    }

    public static function transformCols($cols, $objectName, $maxSizeCols = [], $fieldMaxSize = null, $asCol = false){
        $transformedCols = [];
        foreach ($cols as $col){
            if (is_array($col)){
                $transformedCols[] = Utl::substitute(reset($col), [self::colsPrefix($colString = key($col), $objectName) . $colString]);
            }else{
                $extCol = ($colPrefix = self::colsPrefix($col, $objectName)) . $col;
                $asColString = $asCol && !empty($colPrefix) ? " as $col" : '';
                $transformedCols[] = in_array($col, $maxSizeCols)
                    ? "if(length($extCol) > $fieldMaxSize , concat(\"#tukos{id:\", tukos.id, \",object:$objectName,col:$col}\"), $extCol) $asColString"
                    : "$extCol$asColString";
            }
        }
        return $transformedCols;
    }

    public static function transformWhere($where, $objectName, $noPrefix = false){
        $transformedWhere = [];
        $colWhere = $noPrefix ? function($key){return $key;} : function($key) use ($objectName){
            return self::colsPrefix($key, $objectName) . $key;
        };
        foreach ($where as $key => $condition){
            if (is_string($key)){// is an elementary condition
                if (is_array($condition)){// transform from [$col => [$opr, $value]] into ['col' => $col, 'opr' => $opr, 'values' => $values]
                    if (!empty($condition[0])){
                		$transformedWhere[] = self::longFilter($colWhere($key), $condition);
                    }
                }else if ($key === 'or'){
                    $transformedWhere[$key] = $condition;
                }else if ($where[$key] !== '%'){// is a simple where
                    $transformedWhere[$colWhere($key)] = $condition;
                }
            }else{// is a complex elementary condition or a nested condition
                reset($condition);
                if (is_string(key($condition))){// is a complex elementary condtion
                	if (isset($condition['col'])){
                		$condition['col'] = $colWhere($condition['col']);
                	}
                	if (in_array($condition['opr'], ['IN SELECT', 'NOT IN SELECT', 'EXISTS', 'NOT EXISTS']) && is_array($condition['values'])){
                	    self::$hasSubSelect = true;
                	    $hasObjectCols = self::$hasObjectCols;
                	    $hasTukosCols = self::$hasTukosCols;
                	    self::resetCols();
                	    $condition['values'] = self::transformGet($condition['values'], $condition['values']['table'], [], 0, false);
                	     self::$hasObjectCols = $hasObjectCols;
                	     self::$hasTukosCols = $hasTukosCols;
                	}
                    $transformedWhere[] = $condition;
                }else{//is a nested condition
                    $transformedWhere[] = self::transformWhere($condition, $objectName);
                }
            }
        }
        return $transformedWhere;
    }
    
    public static function longFilter($col, $shortFilter){
    	$opr = $shortFilter[0];
        if (!empty($shortFilter[1])){
    		$values = $shortFilter[1];
    		if ($opr === 'RLIKE' && strpos($values, '~') !== false){
    		    return array_map(function($value) use ($col, $opr){return  ['col' => $col, 'opr' =>  $opr, 'values' =>  $value];}, explode('~', $values));
    		}else{
    	        return ['col' => $col, 'opr' => $opr, 'values' => $shortFilter[1]];
    		}
    	}else{
    		return [['col' => $col, 'opr' => $opr, 'values' => ''], ['col' => $col, 'opr' => 'IS NULL', 'values' => null, 'or' => true]];
    		
    	}
    }
    
    public static function transformItem($item, $objectName, &$queryAtts){
        foreach ($item as $col => $value){
            $transformedCol = self::colsPrefix($col, $objectName) . $col;
            $transformedItem[$transformedCol] = $value;
            //$queryAtts['set'][$transformedCol] = $value;
        }
        return $transformedItem;
    }

    
}
?>
