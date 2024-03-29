<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class GetItems extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->getViewModel  = $controller->objectsStore->objectViewModel($controller, 'Edit', 'GetItems');
    }
    function response($query){
        $response = [];
        $this->getViewModel->respond($response, $query);
        return $response;
    }
}
?>
