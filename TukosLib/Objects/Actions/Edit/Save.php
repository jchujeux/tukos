<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;

class Save extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->saveViewModel = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Save');
        $this->getViewModel  = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Get');
    }
    function response($query){
        $savedId = $this->saveViewModel->save($query);
        if ($savedId){
            if (is_array($savedId)){
                return $savedId;
            }else{
                $response = [];
                $this->getViewModel->respond($response, ($isBackOffice = $this->request['object'] === 'backoffice') ? array_merge($query, ['id' => $savedId]) : ['id' => $savedId]);
                if (!$isBackOffice){
                    $response['title'] = $this->view->tabEditTitle($response['data']['value']);
                }
                return $response;
            }
        }else{
            return ['data' => false];
        }
    }
}
?>
