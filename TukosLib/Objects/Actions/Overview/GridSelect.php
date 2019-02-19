<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\Objects\Views\Overview\Models\Get as OverviewGetModel;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class GridSelect extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->actionModel  = new OverviewGetModel($this);
    }
    function response($query){
        $result = $this->actionModel->getOverviewGrid($query);
        //Feedback::reset();// or else the feedback is added to the JsonRest response and screws-up the dgrid
        return $result;
    }
}
?>
