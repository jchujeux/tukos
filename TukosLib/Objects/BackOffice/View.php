<?php
namespace TukosLib\Objects\BackOffice;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\Directory;
use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;

class View extends ObjectTranslator{

     private $backOfficeProperties = ['dataWidgets', 'actionWidgets',  'dataLayout', 'actionLayout', 'summaryLayout'];
    function __construct($objectName, $translator=null){
        $this->objectName = $objectName;
        parent::__construct($objectName, $translator);
        $this->subObjects = [];
    }
    function __call($func, $arguments){
        if (in_array($func, $this->backOfficeProperties)){
            return $this->backOfficeProperty($func, $arguments[0], isset($arguments[1]) ? $arguments[1] : []);
        }
    }
    function backOfficeProperty($property, $query, $default = []){
        $this->instantiateBackOffice($query);
        return isset($this->backOffice->$property) ? $this->backOffice->$property : $default;
    }
    function instantiateBackOffice($query){
        if (empty($this->backOffice)){
            list($object, $form) = array_values(Utl::getItems(['object', 'form'], Utl::getItem('params', $query, $query)));
            $backOfficeClass = 'TukosLib\\Objects\\' . Directory::getObjDir($object) . '\\BackOffice\\' . $form;
            $this->backOffice = new $backOfficeClass($query);
            $this->dataWidgets = $this->backOffice->dataWidgets;
        }
    }
    function getDataWidgets($query){
        $this->instantiateBackOffice($query);
        return $this->backOffice->dataWidgets;
    }
    function dataWidgetsNames($query){
        $this->instantiateBackOffice($query);
        return array_keys($this->backOffice->dataWidgets);
    }
    function dataElts($query){
        $this->instantiateBackOffice($query);
        return $this->backOffice->dataElts;
    }
    function sendOnSave($query){
        $this->instantiateBackOffice($query);
        return $this->backOffice->sendOnSave();
    }
    function sendOnReset($query){
        $this->instantiateBackOffice($query);
        return $this->backOffice->sendOnReset();
    }
    function widgetsDescription($query, $editOnly = true){
        $this->instantiateBackOffice($query);
        $result = [];
        foreach ($this->backOffice->dataWidgets as $id => $dataWidget){
            $result[$id] = Widgets::description($dataWidget, $editOnly);
        }
        return $result;
    }
    function getDataLayout($query){
        $this->instantiateBackOffice($query);
        return isset($this->backOffice->dataLayout) ? $this->backOffice->dataLayout : [];
    }
    function getActionLayout($query){
        $this->instantiateBackOffice($query);
        return isset($this->backOffice->actionLayout) ? $this->backOffice->actionLayout : [];
    }
    function getActionWidgets($query){
        $this->instantiateBackOffice($query);
        return $this->backOffice->getActionWidgets($query);
    }
    function getOverviewDataLayout($query){
        
    }
    function getTitle($query){
        $this->instantiateBackOffice($query);
        return $this->backOffice->getTitle();
    }
    function tabEditTitle ($values){
        $title =  Utl::getItem('name', $values, '');
        return  strlen($title) > 20 ? substr($title, 0, 17) . ' ...' : $title;
    }
}
?>
