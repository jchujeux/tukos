<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class Edit extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->getViewModel  = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Get');
    }
    function response($query){
        $response = [];
        $this->getViewModel->respond($response, $query);
        $response['title'] = $this->view->tabEditTitle($response['data']['value']);
        return $response;
    }
}
?>
