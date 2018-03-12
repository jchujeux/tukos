<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\Objects\Views\Overview\Models\Get as OverviewGetModel;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Delete extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->actionModel  = new OverviewGetModel($this);
    }
    function response($query){
        $selectedIds = $this->dialogue->getValues()['ids'];
        $result = $this->model->delete([['col' => 'id', 'opr' => 'IN', 'values' => $selectedIds]]);
        if ($result === 0){
            Feedback::add($this->view->tr('Nothingtodelete!'));
        }else{
            Feedback::add([$this->view->tr('DoneEntriesDeleted') => $result]);
        }
        //return ['data' => $this->actionModel->get()];
        return [];
    }
}
?>
