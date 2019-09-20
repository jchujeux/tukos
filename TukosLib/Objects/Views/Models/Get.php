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
            return  $this->model->getCombinedCustomization($query, $this->controller->request['view'], $this->paneMode, ['widgetsDescription']);
        }else{
            return $this->user->getCustomView($this->view->objectName, $this->controller->request['view'], $this->paneMode, ['widgetsDescription']);
        }
    }

    function getOne($query, $cols, $modelToView, $silent = false, $historyModelToView = true){

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

        if ($historyModelToView && isset($value['history'])){
            $value['history'] = $this->modelToView($value['history'], 'objToOverview', true);
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
