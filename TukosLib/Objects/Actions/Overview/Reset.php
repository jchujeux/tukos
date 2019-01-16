<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\Objects\Views\Overview\Models\Get as OverviewGetModel;
use TukosLib\TukosFramework as Tfk;

class Reset extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->actionModel  = new OverviewGetModel($this);
    }
    function response($query){
        if (isset($query['params']) && !empty($query['params']['process'])){
        	$process = $query['params']['process'];
        	$this->view->model->$process($this->dialogue->getValues());
        }
    	return [];
    }
}
?>
