<?php
namespace TukosLib\Objects;

use TukosLib\Utils\Utilities as Utl;

use TukosLib\TukosFramework as Tfk;

class StoreUtilities {

    public static $tukosModel = null;
    public static $tukosTableName = null;
    public static $store = null;
    public static $hasObjectCols = false;
    public static $hasTukosCols = false;
    public static $hasSubSelect = false;
    private static $idColsCache = [];
    
    public static function instantiate(){
        if (is_null(self::$tukosModel)){
            self::$tukosModel = Tfk::$registry->get('tukosModel');
            self::$tukosTableName = self::$tukosModel->tableName();
            self::$store = self::$tukosModel->store;
        } 
    }

	private static function translatedExtendedIds($ids){
        if (empty($ids)){
        		return [];
        }else{
			$result = [];
            $values = self::extendedNameCols($ids);
	        $extendedNameColsCache = [];
	    	$objectStore = Tfk::$registry->get('objectsStore');
	        foreach ($values as $objectName => $objectValues){
	    		$model = $objectStore->objectModel($objectName);
	        	$extendedNameIdCols = array_intersect($model->extendedNameCols, $model->idCols);
	        	foreach ($objectValues as $id => $extendedNameValues){
		        	$presentExtendedNameIdCols = array_intersect($extendedNameIdCols, array_keys($extendedNameValues));
		        	foreach ($presentExtendedNameIdCols as $col){
						$idColValue = $extendedNameValues[$col];
		        		$extendedNameValues[$col] = implode(' ', self::extendedNameColsForId($idColValue, $values, $extendedNameColsCache));
		        	}	
		        	$result[$id] = ['name' => implode(' ', $extendedNameValues), 'object' => $objectName];
		        }
	        }
	        return $result;
        }
    }
    
    function extendedNameColsForId($id, $values, $extendedNameColsCache){
   		if (isset($extendedNameColsCache[$id])){
   			return $extendedNameColsCache[$id];
   		}else{
   			foreach($values as $objectName => $objectValues){
   				if (isset($objectValues[$id])){
   					$extendedNameColsCache[$id] = $objectValues[$id];
   					return $extendedNameColsCache[$id];
   				}
   			}
   			$extendedNameColsCache[$id] = [];
   			return [];
   		}
    }
    
    function objectTranslatedExtendedNames($model, $storeAtts){
    	$objectName = $model->objectName;
    	$requestedCols = $storeAtts['cols'];
    	$storeAtts['cols'] = array_unique(array_merge(Utl::getItem('cols', $storeAtts, ['id']), $model->extendedNameCols));
    	$values = Utl::toAssociative($model->translateAll($model->getAllExtended($storeAtts)), 'id');
    	if (empty($values)){
    		return [];
    	}else{
	    	$extendedNameIdCols = array_intersect($model->extendedNameCols, $model->idCols);
	    	if (!empty($extendedNameIdCols)){
	    		$extraIds = [];
	    		$extendedNameColsCache = [];
	    		foreach($extendedNameIdCols as $col){
	    			$extraIds = array_filter(array_unique(array_merge($extraIds, array_unique(array_column($values, $col)))));
	    		}
	    		if(!empty($extraIds)){
	    			$objectIds = array_keys($values);
	    			$missingIds = array_diff($extraIds, $objectIds);
	    			if (!empty($missingIds)){
	    				$extraValues = self::extendedNameCols($missingIds);
	    			}
	    			$idsInValues = array_intersect($objectIds, $missingIds);
	    			foreach ($idsInValues as $id){
    					$extraValues[$objectName][$id] = values[$id];
	    			}
	    		}else{
	    			$extraValues = [];
	    		}
	    	}
			$result = [];
			$keepName = in_array('name', $requestedCols);
			$colsToRemove = array_diff($model->extendedNameCols, $requestedCols);
			foreach ($values as $id => $value){
			    if ($keepName){
			        $value['sname'] = $value['name'];
			    }
			    $extendedNameValues = Utl::getItems($model->extendedNameCols, $value);
			    $presentExtendedNameIdCols = array_intersect($extendedNameIdCols, array_keys($extendedNameValues));
		        foreach ($presentExtendedNameIdCols as $col){
					$idColValue = $extendedNameValues[$col];
		        	$extendedNameValues[$col] = implode(' ', self::extendedNameColsForId($idColValue, $extraValues, $extendedNameColsCache));
		        }	
		        Utl::extractItems($colsToRemove, $value);
		        $result[$id] = array_merge($value, ['name' => implode(' ', $extendedNameValues)]);
			}
			return $result;
    	}
    }

    function translatedExtendedName($model, $id){
    	$extendedNameValues = $model->getOne(['where' => ['id' => $id], 'cols' => $model->extendedNameCols]);
    	$model->translateOne($extendedNameValues);
    	if (empty($extendedNameValues)){
    		return [];
    	}else{
    		$extendedNameIdCols = array_intersect($model->extendedNameCols, $model->idCols);
    		if (!empty($extendedNameIdCols)){
    			$extraIds = [];
    			$extendedNameColsCache = [];
    			foreach($extendedNameIdCols as $col){
    				$extraIds = array_filter(array_unique(array_merge($extraIds, [$extendedNameValues[$col]])));
    			}
    			if(!empty($extraIds)){
    				$extraValues = self::extendedNameCols($extraIds);
    			}else{
    				$extraValues = [];
    			}
    		}
  			$presentExtendedNameIdCols = array_intersect($extendedNameIdCols, array_keys($extendedNameValues));
   			foreach ($presentExtendedNameIdCols as $col){
   				$idColValue = $extendedNameValues[$col];
   				$extendedNameValues[$col] = implode(' ', self::extendedNameColsForId($idColValue, $extraValues, $extendedNameColsCache));
   			}
   			return implode(' ', $extendedNameValues);
    	}
    }
    
    function extendedNameCols($ids){
    	$objectNamesAndIds = Utl::toAssociativeGrouped(self::$tukosModel->getAll(['where' => [['col' => 'id', 'opr' => 'IN', 'values' => $ids]], 'cols' => ['id', 'object'], 'union' => self::$tukosModel->parameters['union']]), 'object', 'true');
    	$objectStore = Tfk::$registry->get('objectsStore');
    	$extraIds = $values = [];
    	foreach ($objectNamesAndIds as $objectName => $objectIds){
    		$model = $objectStore->objectModel($objectName);
    		$values[$objectName] = Utl::toAssociative($model->translateAll($model->getAll(['where' => [['col' => 'id', 'opr' => 'IN', 'values' => $objectIds]], 'cols' => array_merge(['id'], $model->extendedNameCols)])), 'id');
    		$extendedNameIdCols = array_intersect($model->extendedNameCols, $model->idCols);
    		if (!empty($extendedNameIdCols)){
    			foreach($extendedNameIdCols as $col){
    				$extraIds = array_filter(array_unique(array_merge($extraIds, array_unique(array_column($values[$objectName], $col)))));
   				}
   				$missingIds = array_diff($extraIds, $ids);
    		}
   		}
   		if (!empty($missingIds)){
   			$extraValues = self::extendedNameCols($missingIds);
   			return Utl::array_merge_recursive_replace($values, $extraValues);
   		}else{
   			return $values;
   		}
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
        if ($union = Utl::getItem('union', $queryAtts)){
            self::$hasSubSelect = false;
            $cols = $queryAtts['cols'];
        }
        $queryAtts['where'] = self::transformWhere(self::deletedFilter($queryAtts['where']), $objectName);
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
            $queryAtts['join'][] = ['inner', self::$tukosTableName, self::$tukosTableName . '.id = ' . $objectName . '.id'];
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
  
    public static function deletedFilter($where){
    	$where[] = ['col' => 'id', 'opr' => '>', 'values' => 0];
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
                $extCol = self::colsPrefix($col, $objectName) . $col;
                $asColString = $asCol ? " as $col" : '';
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
