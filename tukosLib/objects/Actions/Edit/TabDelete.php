<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\Edit\Tab;
use TukosLib\Objects\Views\Models\Delete as DeleteModel;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class TabDelete extends AbstractAction{
    function response($query){
        $result = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Delete')->deleteOne($this->dialogue->getValues());
        if ($result){
            Feedback::add([$this->view->tr('DoneNumberOfEntriesDeleted') => $result]);
            return parent::response($query);
        }else{
            return [];
        }
    }
}
?>
