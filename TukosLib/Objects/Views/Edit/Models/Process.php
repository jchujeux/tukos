<?php

namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Utils\Utilities as Utl;


class Process extends AbstractViewModel {

    function __construct($controller, $params=[]){
        parent::__construct($controller, $params);
        $this->model    = (empty($params['model'])  ? $controller->model : $params['model']);
        $this->modelProcess  = (empty($params['process'])  ? 'processOne' : $params['process']);
    }
    function process($query){
    	$contextPathId = (isset($query['contextpathid']) ?  Utl::extractItem('contextpathid', $query) : $this->user->getContextId($this->model->objectName));
    	$process = (empty($query['params']['process']) ? $this->modelProcess : $query['params']['process']);
        return $this->model->$process($query, $this->dialogue->getValues());
    }
}
?>
