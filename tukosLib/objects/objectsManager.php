<?php
namespace TukosLib\Objects;

use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class ObjectsManager{
    function __construct($store=null){
        $this->models               = [];
        $this->views                = [];
        $this->tabControllers       = [];
        $this->dialogueControllers  = [];
        $this->store = ($store ? $store : Tfk::$registry->get('store'));
    }

    function objectModel($objectName, $translator=null){
        if (!isset($this->models[$objectName])){
            $modelClass = 'TukosLib\\Objects\\' . Directory::getObjDir($objectName) . '\\Model';
            $this->models[$objectName] = new $modelClass($objectName, $translator);
        }
        return $this->models[$objectName];
    }

    function objectView($objectName, $translator=null){
        if (!isset($this->views[$objectName])){
            $viewClass  = 'TukosLib\\Objects\\' . Directory::getObjDir($objectName) . '\\View';
            $this->views[$objectName] = new $viewClass($objectName, $translator);
        }
        return $this->views[$objectName];
    }

    function objectController($objectName){
        $controllerClass = 'TukosLib\\Objects\\Controller';
        return new $controllerClass($objectName);
    }

    function objectAction($controller, $request){
        try{
            $actionClass  = 'TukosLib\\Objects\\' . Directory::getObjDir($controller->objectName) . '\\Actions\\' . $request['view'] . '\\' . $request['action'];
            return new $actionClass($controller);
        }catch(\Exception $e){
            $defaultActionClass     = 'TukosLib\\Objects\\Actions\\'     . $request['view'] . '\\' . $request['action'];
            return new $defaultActionClass($controller);
        }            
    }
    function objectActionView($controller){
        if ($controller->request['view'] !== 'noview'){
            try{
                $actionViewClass  = 'TukosLib\\Objects\\' . Directory::getObjDir($controller->objectName) . '\\Views\\' . $controller->request['view'] . '\\View';
                return new $actionViewClass($controller);
            }catch(\Exception $e){
                $defaultActionViewClass     = 'TukosLib\\Objects\\Views\\'     . $controller->request['view'] . '\\View';
                return new $defaultActionViewClass($controller);
            }
        }            
    }

    function objectViewModel($controller, $view, $viewModelClassName, $params=[]){
        try{
            $viewModelClass  = 'TukosLib\\Objects\\' . Directory::getObjDir($controller->objectName) . '\\Views\\' . $view . '\\Models\\' . $viewModelClassName;
            return new $viewModelClass($controller, $params);
        }catch(\Exception $e){
            $viewModelClass     = 'TukosLib\\Objects\\Views\\'     . $view . '\\Models\\' . $viewModelClassName;
            return new $viewModelClass($controller, $params);
        }            
    }
}

?>
