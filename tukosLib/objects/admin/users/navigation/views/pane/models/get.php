<?php

namespace TukosLib\Objects\Admin\Users\Navigation\Views\Pane\Models;

use TukosLib\Objects\Views\Models\Get as ViewsGetModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;

class Get extends ViewsGetModel {
    function childrenItems($where, $objectName){
        $where = SUtl::transformWhere($where, 'unused');
    	return SUtl::$store->getAll([
            'cols'  => ['tukos.id', 'tukos.object', 'tukos.name', 'count(*)-1 as children'],
            'table' => SUtl::$tukosTableName,
            'join'  => ['inner', SUtl::$tukosTableName . ' as t0', 'tukos.id = t0.parentid or tukos.id = t0.id'], 
            'where' => array_merge($where, [['col' => 't0.id', 'opr' => '>', 'values' => 0], 'tukos.object' => $objectName]),
        	'groupBy' => ['tukos.id']
            ]
        );
    }

    function childrenObjects($where, $excludeObjects = []){
        //$atts = ['where' => SUtl::deletedFilter($this->user->filter(['parentid' => $parentId], 'tukos', 'tukos')), 'cols' => ['object', 'parentid', 'count(*) as children'], 'groupBy' => ['object']];
        $atts = ['where' => $where, 'cols' => ['object', 'parentid', 'count(*) as children'], 'groupBy' => ['object']];
        if (!empty($excludeObjects)){
            $atts['where'][] = ['col' => 'object', 'opr' => 'NOT IN', 'values' => $excludeObjects];
        }
        return SUtl::$tukosModel->getAll($atts);
    }

    function get($query){
        if ($query['params']['get'] == 'getPath'){
            return $this->getPath($query);
        }else{
            //$parentId = $query['storeatts']['where']['parentid'];
            $where = SUtl::deletedFilter($this->user->filter($query['storeatts']['where']));
            $children = [];
            foreach ($query['params']['get'] as $itemOrObject){
                if ($itemOrObject === 'items'){
                    $children = array_merge($children, $this->childrenItems($where, $query['params']['object']));
                }else if ($itemOrObject === 'objects'){
                    $children = array_merge($children, $this->childrenObjects($where, isset($query['params']['object']) ? [$query['params']['object']] : []));
                }
            }
            return ['items' => $children];
        }
    }

    function getPath($query){
        return ['path' => array_reverse($this->model->itemColPath(SUtl::$tukosModel->getOne(['where' => ['id' => $query['id']], 'cols' => ['id', 'object', 'name', 'parentid']]), 'parentid'))];
    }

}
?>
