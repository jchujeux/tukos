<?php

namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Objects\Views\Models\Get as ViewsGetModel;
use TukosLib\Objects\Views\Edit\Models\SubObjectsGet;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;

class GetItem extends ViewsGetModel {

    public function respond(&$response, $query, $cols=['*']){
        if (!empty($query['params'])){
            $this->modelGetOne  = (empty($query['params']['getOne'])  ? $this->modelGetOne : $query['params']['getOne']);
            $this->modelGetAll  = (empty($query['params']['getAll'])  ? $this->modelGetAll : $query['params']['getAll']);
        }
        $this->getData($response, $query, $cols);
    }
    protected function getData(&$response, $query, $cols=['*']){
        $contextPathId = (isset($query['contextpathid']) ?  Utl::extractItem('contextpathid', $query) : $this->user->getContextId($this->model->objectName));
        $storeAtts = Utl::extractItem('storeatts', $query, []);
        $where = Utl::getItem('where', $storeAtts, $query);
        $cols = Utl::getItem('cols', $storeAtts, $cols);
        if ($key = array_search('extendedName', $cols) !== false){
            $cols[$key] = 'name';
        }
        $value = $this->getOne($where, $cols, 'objToEdit');
        if ($key && $id = Utl::getItem('id', $value, false, false)){
            $value['name'] = SUtl::translatedExtendedNames([$id])[$id];
        }
        if (isset($value['id'])){
        	$customMode = 'item';
        	$allowCustomValue = false;
        }else{
        	$value = $this->initialize('objToEdit', isset($query['storeatts']['init']) ? $query['storeatts']['init'] : []);
        	$customMode = 'object';
        	$allowCustomValue = true;
        }
    	$response['data']['value'] = $value;
        return $response;
    }
}
?>
