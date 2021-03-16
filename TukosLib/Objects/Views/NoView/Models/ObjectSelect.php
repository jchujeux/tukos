<?php

namespace TukosLib\Objects\Views\NoView\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;

class ObjectSelect extends AbstractViewModel{

    function modelToViewSingle($values){
        return $this->convert($values, $this->view->dataWidgets, 'objToSelect', false, [], false); 
    }
    function modelToViewMulti($values){
        return $this->convert($values, $this->view->dataWidgets, 'objToSelect', true, [], false); 
    }    
	function get($query, $allDescendants = false){
		$storeAtts = $query['storeatts'];
		$storeAtts['where'] = $this->user->filter($storeAtts['where'], $this->model->objectName);
		if (Utl::getItem('one', $query)){// A single row is expected (example: JsonRest get function)
			return $this->modelToViewSingle($this->model->getOneExtended($storeAtts));
		}else{// An array of rows is expected (example: JsonREST query)
			$storeAtts['allDescendants'] = $allDescendants;
			$whereName = substr(Utl::extractItem('name', $storeAtts['where'])[1], 1, -1);
			if (isset($storeAtts['where']['in'])){
				$sourceId = Utl::extractItem('in', $storeAtts['where']);
				$sourceInfo = Utl::extractItem('list', $storeAtts['where']);
				$storeAtts['where'][] = ['col' => 'id', 'opr' => 'in', 'values' => $this->idList($sourceId, $sourceInfo)];
			}
			$result = SUtl::objectTranslatedExtendedNames($this->model, $storeAtts);
			SUtl::resetIdColsCache();
			if (!empty($whereName)){
				$result = array_filter($result, function($item) use ($whereName){
					return stristr($item['name'], $whereName);
				});
			}
			return ['items' => Utl::toNumeric($result, 'id', true), 'total' => count($result)];
		}
	}
	
    function idList($sourceId, $sourceInfo){
        $object = key($sourceInfo);
        $jsonCol = key($sourceInfo[$object]);
        $item = Tfk::$registry->get('objectsStore')->objectModel($object)->getOne(['where' => ['id' => $sourceId], 'cols' => [$jsonCol]], [$jsonCol => []]);
        if (empty($item)){
            return [];
        }else{
            return array_unique(array_column($item[$jsonCol], $sourceInfo[$object][$jsonCol]));
        }
    }
}
?>
