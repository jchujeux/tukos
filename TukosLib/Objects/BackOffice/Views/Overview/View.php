<?php

namespace TukosLib\Objects\BackOffice\Views\Overview;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\Objects\Directory;

class View {

    function __construct($controller){
        $this->controller = $controller;
        $this->view  = $controller->view;
        $this->model = $controller->model;
        $this->user = $controller->user;
        $this->objectName = $controller->objectName;
        $this->paneMode = $controller->paneMode;
    }
    function formContent($query){
        return  [
            'object'         => $this->view->objectName,
            'contextPaths'  => [[0]],
            'viewMode'      => 'Overview',
        	'paneMode' => $this->paneMode,
            'widgetsDescription' => array_merge ($this->view->widgetsDescription($query), $this->view->actionWidgets($query)),                         
            'dataLayout'  => $this->view->dataLayout($query),
            'actionLayout'  => $this->view->actionLayout($query),
            'summaryLayout' => $this->view->summaryLayout($query),
            'style' => ['padding' => '0px']
        ];
    } 
}
?>
