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
        if ($received['ids'] === true){
            $where = array_merge($this->user->getCustomView($this->objectName, 'overview', $this->paneMode, ['data', 'filters', 'overview']), $query['storeatts']['where']);
            $where['contextpathid'] = $query['contextpathid'];
        }else{
            $where = [['col' => 'id', 'opr' => 'IN', 'values' => $received['ids']]];
        }
        $result = $this->model->updateAll($received['values'], ['where' => $where]);
        Feedback::add($this->view->tr('DoneModified'));
        if ($result === 0){$this->dialogue->response->setStatusCode(404);}
        //return ['data' => $this->actionModel->get()];
        return [];
    }
}
?>
