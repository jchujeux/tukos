<?php

namespace TukosLib\Objects\Itm\Itsm\incidents\Views\Edit\Models;

use TukosLib\Objects\Views\Edit\Models\Get as EditGet;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Get extends EditGet{

    function getOne($query, $cols, $modelToView, $silent = false, $historyModelToView = true){
        if (!in_array('history', $cols)){
            $cols[] = 'history';
            $result = parent::getOne($query, $cols, $modelToView, $silent, false);
            $result['statushistory'] = $this->view->model->subHistory($result, $this->view->statusHistoryCols);
            unset($result['history']);
        }else{
            $result = parent::getOne($query, $cols, $modelToView, $silent, false);
            $result['statushistory'] = $this->view->model->subHistory($result, $this->view->statusHistoryCols);
        }
        $result['history'] = $this->modelToView($result['history'], 'objToOverview', true);
        $result['statushistory'] = $this->modelToView($result['statushistory'], 'objToOverview', true);
        return $result;
    }

}
?>
