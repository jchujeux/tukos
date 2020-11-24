<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\AbstractAction;
//use TukosLib\Objects\Views\Overview\Models\Get as OverviewGetModel;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Restore extends AbstractAction{
    function response($query){
        $received = $this->dialogue->getValues();
        if ($received['ids'] === true){
            $where = $query['storeatts']['where'];
            $where['contextpathid'] = $query['contextpathid'];
        }else{
            $where = [['col' => 'id', 'opr' => 'IN', 'values' => $received['ids']]];
        }
        $where = $this->user->filterContext($this->user->filterReadonly($where));
        $this->model->restore($where);
        return [];
    }
}
?>
