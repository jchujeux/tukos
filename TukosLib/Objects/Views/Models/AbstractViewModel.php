<?php

namespace TukosLib\Objects\Views\Models;

use TukosLib\Objects\Views\Models\ModelsAndViews;
use TukosLib\Utils\Utilities as Utl;
//use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

abstract class AbstractViewModel/* extends ModelsAndViews*/{

    use ModelsAndViews;
    
    function __construct($controller, $params=[]){
        $this->controller = $controller;
        $this->dialogue = $controller->dialogue;
        $this->objectName = $this->controller->objectName;
        $this->user     = $controller->user;
        $this->objectsStore = $controller->objectsStore;
        $this->view     = (empty($params['view'])  ? $controller->view : $params['view']);
        $this->model    = (empty($params['model'])  ? $controller->model : $params['model']);
        $this->paneMode = $controller->paneMode;
    }

    function modelToView($values, $modelToView, $multiRows = false){
        return $this->convert($values, $this->view->dataWidgets, $modelToView, $multiRows, [], false); 
    }

    function viewToModel($values, $viewToModel, $multiRows = false){
        return $this->convert($values, $this->view->dataWidgets, $viewToModel, $multiRows, [], false); 
    }

    public function initialize($modelToView, $init = []){
        return $this->modelToView($this->model->initializeExtended($init), $modelToView, false);
    }
    public function duplicate($id, $modelToView){
        return $this->modelToView($this->model->duplicateOneExtended($id, array_merge($this->view->allowedGetCols(), ['custom']), $this->view->jsonColsPathsView), $modelToView, false);
    }

    public function adjustedCols($colsRequired){
        if($colsRequired === ['*']){
            return $this->view->allowedGetCols();
        }else{
            foreach ($this->view->mustGetCols as $mustGetCol){
                if (!in_array($mustGetCol, $colsRequired)){
                    $colsRequired[] = $mustGetCol;
                    //Feedback::add("added col $mustGetCol in adjustedCols");
                }
            }
            return  array_intersect($colsRequired, $this->view->allowedGetCols());
        }
    }
}
?>
