<?php

namespace TukosLib\Objects\Actions;

use TukosLib\TukosFramework as Tfk;

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
        $this->objectsStore = $this->controller->objectsStore;
        $this->actionView = $this->objectsStore->objectActionView($this);
    }
}
?>
