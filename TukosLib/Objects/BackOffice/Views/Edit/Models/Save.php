<?php

namespace TukosLib\Objects\BackOffice\Views\Edit\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;

class Save extends AbstractViewModel {
    
    function save($query){
        $this->controller->view->instantiateBackOffice($query);
        $valuesToSave = $this->viewToModel($this->dialogue->getValues(), 'editToObj', false);
        return $this->controller->view->backOffice->save($valuesToSave);
    }
}
?>
