<?php

namespace TukosLib\Objects\BackOffice\Views\NoView\Models;

class SendEmail{

    function __construct($controller, $params=[]){
        $this->controller = $controller;
        $this->dialogue = $controller->dialogue;
        $this->model    = (empty($params['model'])  ? $controller->model : $params['model']);
    }
    function get($query){
        return $this->model->sendContent($query, array_merge(['sendas' => 'appendtobody', 'formatas' => 'json'], $this->dialogue->getValues()), ['username' => 'tukosBackOffice']);
    }
}
?>
