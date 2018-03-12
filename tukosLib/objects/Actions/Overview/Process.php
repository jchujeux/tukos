<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\Objects\Views\Overview\Models\Get as OverviewGetModel;
use TukosLib\Utils\Feedback;

class Process extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->actionModel  = new OverviewGetModel($this);
    }
	function response($query){
        $process = isset($query['params']) && !empty($query['params']['process']) ? $query['params']['process'] : 'process';
    	return $this->view->model->$process($query, $this->dialogue->getValues());
    }
}
?>
