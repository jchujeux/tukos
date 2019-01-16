<?php

namespace TukosLib\Objects\Actions;

use TukosLib\TukosFramework as Tfk;
use TukosLib\Utils\Utilities as Utl;

class AbstractAction {
    function __construct($controller){
        $this->controller = $controller;
        $this->objectName = $controller->objectName;
        $this->tr         = $controller->tr;
        $this->dialogue   = $controller->dialogue;
        $this->user       = Tfk::$registry->get('user');
        $this->model      = $controller->model;
        $this->view       = $controller->view;
        $this->request    = $controller->request;
        $this->paneMode   = $controller->paneMode;
        $this->objectsStore = $this->controller->objectsStore;
        $this->actionView = $this->objectsStore->objectActionView($this);
    }
    function response($query){
        return $this->controller->objectsStore->objectViewModel($this->controller, $this->request['view'], 
                                                                empty($actionModel = Utl::getItem('actionModel', Utl::getItem('params', $query, []))) ? $this->request['action'] : $actionModel)->get($query);
    }
}
?>
