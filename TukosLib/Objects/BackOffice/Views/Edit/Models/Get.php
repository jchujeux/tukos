<?php
namespace TukosLib\Objects\BackOffice\Views\Edit\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;

class Get extends AbstractViewModel{
    public function respond(&$response, $query){
        $this->view->instantiateBackOffice($query);
        $response['data']['value'] = $this->viewToModel($this->view->backOffice->get($query), 'objToEdit');
    }
}
?>
