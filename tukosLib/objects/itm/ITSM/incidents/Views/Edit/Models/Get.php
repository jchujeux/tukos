<?php

namespace TukosLib\Objects\ITM\ITSM\incidents\Views\Edit\Models;

use TukosLib\Objects\Views\Edit\Models\Get as EditGet;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Get extends EditGet{

    function getOne($query, $cols, $modelToView, $silent = false){
        if (!in_array('history', $cols)){
            $cols[] = 'history';
            $result = parent::getOne($query, $cols, $modelToView, $silent);
            $result['statushistory'] = $this->view->model->subHistory($result, $this->view->statusHistoryCols);
            unset($result['history']);
        }else{
            $result = parent::getOne($query, $cols, $modelToView, $silent);
            $result['statushistory'] = $this->view->model->subHistory($result, $this->view->statusHistoryCols);
        }
        return $result;
    }

}
?>
