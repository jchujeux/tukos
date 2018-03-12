<?php

namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Objects\Views\Models\Get as ViewsGetModel;
use TukosLib\Objects\Views\Edit\Models\SubObjectsGet;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class GetHasChanged extends ViewsGetModel {

    function respond($query){
        if (!empty($query['params'])){
            $this->modelGetOne  = (empty($query['params']['getOne'])  ? $this->modelGetOne : $query['params']['getOne']);
            $this->modelGetAll  = (empty($query['params']['getAll'])  ? $this->modelGetAll : $query['params']['getAll']);
        }
        return ['data' => ['value' => $this->getOne($this->dialogue->getValues()['input'], [], 'objToEdit', true)]];
    }
}
?>
