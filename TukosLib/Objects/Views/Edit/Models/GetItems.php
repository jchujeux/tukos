<?php

namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Objects\Views\Models\Get as ViewsGetModel;
use TukosLib\Objects\Views\Edit\Models\SubObjectsGet;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;

class GetItems extends ViewsGetModel {

    public function respond(&$response, $query, $cols=['*']){
        if (!empty($query['params'])){
            $this->modelGetOne  = (empty($query['params']['getOne'])  ? $this->modelGetOne : $query['params']['getOne']);
            $this->modelGetAll  = (empty($query['params']['getAll'])  ? $this->modelGetAll : $query['params']['getAll']);
        }
        $this->getData($response, $query, $cols);
    }
    protected function getData(&$response, $query, $cols=['*']){
        $storeAtts = Utl::extractItem('storeatts', $query, []);
        $where = Utl::getItem('where', $storeAtts, $query);
        $cols = Utl::getItem('cols', $storeAtts, $cols);
        $promoteRestricted = Utl::extractItem('promote', $storeAtts);
        if ($promoteRestricted){
            $toRestore = $this->user->promoteRestricted;
            $this->user->promoteRestricted = $promoteRestricted;
        }
        $values = $this->getGrid(['where' => $where], $cols, true, 'objToStoreEdit');
        if ($promoteRestricted){
            $this->user->promoteRestricted = $toRestore;
        }
        $response['data'] = $values;
        return $response;
    }
}
?>
