<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\Objects\Views\Overview\Models\Get as OverviewGetModel;
use TukosLib\Utils\Feedback;

class Duplicate extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->actionModel  = new OverviewGetModel($this);
    }
    function response($query){
        $selectedIds = $this->dialogue->getValues()['ids'];
        $result = $this->model->duplicate($selectedIds, array_filter($this->view->allowedGetCols(), function($col){return $col !== 'history';}));
        Feedback::add([$this->view->tr('DoneIdsCreated') => $result]);
        return [];
    }
}
?>
