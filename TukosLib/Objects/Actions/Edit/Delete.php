<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\Objects\Views\Models\Delete as DeleteModel;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Delete extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->deleteViewModel = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Delete');
        $this->getViewModel  = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Get');

    }
    function response($query){
        $result = $this->deleteViewModel->deleteOne($this->dialogue->getValues());
        if ($result){
            Feedback::add([$this->view->tr('DoneNumberOfEntriesDeleted') => $result]);
            $response = [];
            $this->getViewModel->respond($response, []);
            $response['title'] = $this->view->tabEditTitle($response['data']['value']);
            return $response;
        }else{
            return [];
        }
    }
}
?>
