<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\Objects\Views\Overview\Models\Get as OverviewGetModel;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Modify extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->actionModel  = new OverviewGetModel($this);
    }
    function response($query){
        $received = $this->dialogue->getValues();
        $result = $this->model->updateAll($received['values'], ['where' => [['col' => 'id', 'opr' => 'IN', 'values' => $received['ids']]]]);
        Feedback::add($this->view->tr('DoneModified'));
        if ($result === 0){$this->dialogue->response->setStatusCode(404);}
        //return ['data' => $this->actionModel->get()];
        return [];
    }
}
?>
