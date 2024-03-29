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
        $received = $this->dialogue->getValues();
        if ($received['ids'] === true){
            //$where = array_merge($this->user->getCustomView($this->objectName, 'overview', $this->paneMode, ['data', 'filters', 'overview']), $query['storeatts']['where']);
            $where = $query['storeatts']['where'];
            $where['contextpathid'] = $query['contextpathid'];
            $where = $this->user->filter($where, $this->objectName);
        }else{
            $where = [['col' => 'id', 'opr' => 'IN', 'values' => $received['ids']]];
        }
        if (method_exists($this->model, 'bulkPreProcess')){
            $this->model->bulkPreProcess();
        }
        $result = $this->model->delete($where);
        if (method_exists($this->model, 'bulkPostProcess')){
            $this->model->bulkPostProcess();
        }
        if ($result === 0){
            Feedback::add($this->view->tr('Nothingtodelete!'));
        }else if ($result !== false){
            Feedback::add([$this->view->tr('DoneEntriesDeleted') => $result]);
        }
        return [];
    }
}
?>
