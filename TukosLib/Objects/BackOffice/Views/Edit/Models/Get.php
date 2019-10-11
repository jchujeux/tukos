<?php
namespace TukosLib\Objects\BackOffice\Views\Edit\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
//use TukosLib\Objects\Views\Models\ModelsAndViews;

class Get extends AbstractViewModel{
    //use ModelsAndViews;
/*
    function __construct($controller, $params=[]){
        $this->controller = $controller;
        $this->view = $controller->view;
    }
*/
    public function respond(&$response, $query){
        $this->view->instantiateBackOffice($query);
        $response['data']['value'] = $this->viewToModel($this->view->backOffice->get($query), 'objToEdit');
    }
}
?>
