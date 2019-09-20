<?php

namespace TukosLib\Objects\BackOffice\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;

use TukosLib\TukosFramework as Tfk;

class Save extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->saveViewModel = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Save');
        $this->getViewModel  = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Get');
    }
    function response($query){
        $newValues = $this->saveViewModel->save($query);
        if ($newValues){
            return $newValues;
/*
            $response = [];
            $this->getViewModel->respond($response, $newValues);
            return $response;
*/
        }else{
            return ['data' => false];
        }
    }
}
?>
