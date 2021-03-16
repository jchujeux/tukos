<?php

namespace TukosLib\Objects\BackOffice\Actions\NoView;

use TukosLib\Utils\Utilities as Utl;

class SendEmail {
    function __construct($controller){
        $this->controller = $controller;
        $this->objectsStore = $this->controller->objectsStore;
        $this->actionView = $this->objectsStore->objectActionView($controller);
    }
    function response($query){
        return $this->controller->objectsStore->objectViewModel($this->controller, $this->controller->request['view'], 
                                                                empty($actionModel = Utl::getItem('actionModel', Utl::getItem('params', $query, []))) ? $this->controller->request['action'] : $actionModel)->get($query);
    }
}
?>
