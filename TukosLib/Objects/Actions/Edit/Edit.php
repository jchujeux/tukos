<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;

class Edit extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->getViewModel  = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Get');
    }
    function response($query){
        $response = [];
        $this->getViewModel->respond($response, $query);
        if ($this->request['object'] !== 'backoffice'){
            $response['title'] = $this->view->tabEditTitle($response['data']['value']);
        }
        return $response;
    }
}
?>
