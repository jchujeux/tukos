<?php

namespace TukosLib\Objects\BackOffice\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;

use TukosLib\TukosFramework as Tfk;

class Reset extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->resetViewModel  = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Get');
    }
    function response($query){
        $response = [];
        $this->resetViewModel->respond($response, $query);
        return $response;
    }
}
?>
