<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;

use TukosLib\TukosFramework as Tfk;

class Save extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->saveViewModel = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Save');
        $this->getViewModel  = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Get');
    }
    function response($query){
        $savedId = $this->saveViewModel->save($query);
        if ($savedId){
            $response = [];
            $this->getViewModel->respond($response, ['id' => $savedId]);
            $response['title'] = $this->view->tabEditTitle($response['data']['value']);
            return $response;
        }else{
            return ['data' => false];
        }
    }
}
?>
