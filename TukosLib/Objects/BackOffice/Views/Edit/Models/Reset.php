<?php
namespace TukosLib\Objects\BackOffice\Views\Edit\Models;

use TukosLib\Utils\Feedback;
use TukosLib\Objects\Views\Models\AbstractViewModel;

class Reset extends AbstractViewModel {
    
    public function respond(&$response, $query){
        $this->controller->view->instantiateBackOffice($query);
        $response['data']['value'] = $this->controller->view->backOffice->get($query, $this->viewToModel($this->dialogue->getValues(), 'editToObj', false));
    }
}
?>
