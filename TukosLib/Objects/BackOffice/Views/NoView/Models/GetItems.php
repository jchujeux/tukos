<?php
namespace TukosLib\Objects\BackOffice\Views\NoView\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;

class GetItems extends AbstractViewModel{
    public function get($query){
        $this->view->instantiateBackOffice($query);
        return $this->viewToModel($this->view->backOffice->get($query), 'objToEdit');
    }
}
?>
