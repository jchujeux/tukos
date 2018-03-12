<?php

namespace TukosLib\Objects\Views\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Get extends AbstractViewModel {

    function __construct($controller, $params=[]){
        parent::__construct($controller, $params);
        $this->modelGetOne   = (empty($params['getOne'])  ? 'getOneExtended' : $params['getOne']);
        $this->modelGetAll   = (empty($params['getAll'])  ? 'getAllExtended' : $params['getAll']);
    }

    public function getElementsCustomization($query = false){ 
        if ($query){
            return  $this->controller->model->getCombinedCustomization($query, $this->controller->request['view'], ['widgetsDescription']);
        }else{
            return $this->view->user->getCustomView($this->view->objectName, $this->controller->request['view'], ['widgetsDescription']);
        }
    }
/*
    public function hiddenGridCols($widgetName, $query = false){
        $customElements = $this->getElementsCustomization($query);
        $hiddenCols = [];
        if (isset($customElements[$widgetName]['atts']['columns'])){
            foreach ($customElements[$widgetName]['atts']['columns'] as $col => $atts){
                if (!empty($atts['hidden'])){
                    $hiddenCols[] = $col;
                }
            }
        }
        return $hiddenCols;
    }
    
    public function unHiddenGridCols($widgetName, $cols, $query = false){
        $hiddenCols = $this->hiddenGridCols($widgetName, $query);
        $parentidKey = array_search('parentid', $hiddenCols);
        if ($parentidKey){
            unset($hiddenCols[$parentidKey]);
        }
        return array_diff($cols, $hiddenCols);
    }
*/
    function getOne($query, $cols, $modelToView, $silent = false){

        $getOne = $this->modelGetOne;
        $value = $this->modelToView(
            $this->model->$getOne(['where' => $this->user->filter($query, $this->model->objectName), 'cols' => $this->adjustedCols($cols)], $this->view->jsonColsPathsView),
            $modelToView,
            false
        );
        if (!$silent){
            if (empty($value)){
                $value = $this->initialize($modelToView);
                Feedback::add([$this->view->tr('objectNotFound') => json_encode($query)]);
            }else{
                Feedback::add([$this->view->tr('doneObjectFetched') => json_encode($query)]);
            }
        }
        if (isset($value['history'])){
            $value['history'] = $this->modelToView($value['history'], $modelToView, true);
        }
        return $value;
    }

    function getGrid($storeAtts, $cols, $allDescendants, $modelToView){
        $storeAtts['where'] = $this->user->filter($storeAtts['where'], $this->model->objectName);
        $storeAtts['cols'] = $this->adjustedCols($cols);
        $storeAtts['allDescendants'] = $allDescendants;

        $getAll = $this->modelGetAll;
        $result = $this->model->$getAll($storeAtts);

        $result = $this->modelToView($result, $modelToView, true);
        foreach ($result as $i => $row){
            if (isset($row['permission'])){
                $result[$i]['canEdit'] = $this->user->canEdit($row['permission'], $row['updator']);
            }else{
                $result[$i]['canEdit'] = true;
            }
        }
        if (! empty($storeAtts['range'])){
            return ['items' => $result, 'total' => $this->model->foundRows()];
        }else{
            return ['items' => $result];
        }
    }
}
?>
