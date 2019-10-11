<?php
namespace TukosLib\Objects\BackOffice;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\Directory;
use TukosLib\Utils\Widgets;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends ObjectTranslator{

    function __construct($objectName, $translator=null){
        $this->objectName = $objectName;
        parent::__construct($objectName, $translator);
        $this->subObjects = [];
    }
    function instantiateBackOffice($query){
        if (empty($this->backOffice)){
            $backOfficeClass = 'TukosLib\\Objects\\' . Directory::getObjDir($query['object']) . '\\BackOffice\\' . $query['form'];
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
    function getTitle($query){
        $this->instantiateBackOffice($query);
        return $this->backOffice->getTitle();
        
    }
    function tabEditTitle ($values){
        return '';
    }
    
}
?>
