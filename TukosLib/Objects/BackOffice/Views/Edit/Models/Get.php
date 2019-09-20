<?php
namespace TukosLib\Objects\BackOffice\Views\Edit\Models;

use TukosLib\Utils\Feedback;
use TukosLib\Objects\StoreUtilities as SUtl;

class Get {

    function __construct($controller, $params=[]){
        $this->controller = $controller;
    }
    public function respond(&$response, $query){
        $this->controller->view->instantiateBackOffice($query);
        $response['data']['value'] = $this->controller->view->backOffice->get($query);
    }
}
?>
