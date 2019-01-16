<?php

namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Objects\Views\Models\Get as ViewsGetModel;
use TukosLib\Objects\Views\Edit\Models\SubObjectGet;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class SubObject extends ViewsGetModel {
    use SubObjectGet;

    private function filterParentValues($filters, $where){
        $filterParentCols = self::filterParentCols($filters);
        if (!empty($filterParentCols)){
            return $this->getOne($where, $filterParentCols, 'objToEdit');
        }else{
            return [];
        }
    }

    private function addFilterParentValues($filters, $where, $values){
        $colsToGet = array_diff(self::filterParentCols($filters), array_keys($values));
        if (!empty($colsToGet)){
            return array_merge($values, $this->getOne($where, $colsToGet, 'objToEdit'));
        }else{
            return $values;
        }
    }

    public function getChildren($query){
        $contextPathId = (isset($query['contextpathid']) ?  Utl::extractItem('contextpathid', $query) : $this->user->getContextId($this->model->objectName));
        
        $widgetName = $query['subObjectWidget'];
        $subObject = $this->view->subObjects[$widgetName];
        $getClass = $this->objectsStore->objectViewModel($this->controller, 'Edit', 'Get', ['view' => $subObject['view'], 'model' => $subObject['model']]);
        $childrenQuery = self::setQuery(
            $subObject['filters'],
            $this->addfilterParentValues($subObject['filters'], ['id' => $query['id']], ['id' => $query['id'], 'parentid' => $query['parentid']]),
            $contextPathId
        );
        $childrenQuery['parentid'] = $query['parentid'];
        return $getClass->getGrid(
            ['where' => $childrenQuery], 
            self::colsToSend($this, $subObject, $widgetName), //$this->unHiddenGridCols($widgetName, $subObject['getCols'], ['id' => $query['id']]),
            (isset($subObject['allDescendants']) ? $subObject['allDescendants'] : false), 
            'objToStoreEdit'
        );
    }
   
}
?>
