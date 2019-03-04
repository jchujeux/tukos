<?php

namespace TukosLib\Objects\Admin\Users\Navigation\Views\Pane\Models;

use TukosLib\Objects\Views\Models\Get as ViewsGetModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;

class Get extends ViewsGetModel {
    function childrenItems($where, $objectName){
		$model = $this->objectsStore->objectModel($objectName);
    	$childrenNames= SUtl::objectTranslatedExtendedNames($model, ['where' => $where]);
    	$where = SUtl::transformWhere(SUtl::deletedFilter($where), 'unused');
    	$childrenAtts = Utl::toAssociative(SUtl::$store->getAll([
            'cols'  => ['tukos.id', 'tukos.permission', 'tukos.updator', 'count(*)-1 as children'],
            'table' => SUtl::$tukosTableName,
            'join'  => ['inner', SUtl::$tukosTableName . ' as t0', 'tukos.id = t0.parentid or tukos.id = t0.id'], 
            'where' => array_merge($where, [['col' => 't0.id', 'opr' => '>', 'values' => 0], 'tukos.object' => $objectName]),
        	'groupBy' => ['tukos.id']
        ]), 'id');
    	$result = [];
    	foreach ($childrenNames as $id => $name){
    		$atts = $childrenAtts[$id];
    		$result[] = array_merge($name, ['id' => $id, 'object' => $objectName/*$this->view->tr($objectName)*/, 'children' => $atts['children'], 'canEdit' => $this->user->canEdit($atts['permission'], $atts['updator'])]);
    	}
    	return $result;
    }

    function childrenObjects($where, $excludeObjects = []){
        //$atts = ['where' => SUtl::deletedFilter($this->user->filter(['parentid' => $parentId], 'tukos', 'tukos')), 'cols' => ['object', 'parentid', 'count(*) as children'], 'groupBy' => ['object']];
        $atts = ['where' => $where, 'cols' => ['object', 'parentid', 'count(*) as children'], 'groupBy' => ['object']];
        if (!$this->user->isSuperAdmin()){
        	$atts['cols'][] = Utl::substitute('count(CASE WHEN permission = "RO" AND ${userid} <> updator THEN 1 END) AS readonly', ['userid' => $this->user->id()]);
        }
        if (!empty($excludeObjects)){
            $atts['where'][] = ['col' => 'object', 'opr' => 'NOT IN', 'values' => $excludeObjects];
        }
        $result = SUtl::$tukosModel->getAll($atts);
        foreach($result as &$item){
        	$item['object'] = $this->view->tr($item['object']);
        	if (Utl::extractItem('readonly', $item) > 0){
        		$item['canEdit'] = false;
        	}
        }
        return $result;
    }

    function get($query){
        if ($query['params']['get'] == 'getPath'){
            return $this->getPath($query);
        }else{
            //$parentId = $query['storeatts']['where']['parentid'];
            if (isset($query['params']['object'])){
            	$utr = Tfk::$registry->get('translatorsStore')->untranslator('TukosLib', ['tukosLib']);
            	$object = $utr($query['params']['object']);
            }
        	$where = $this->user->filter($query['storeatts']['where']);
            $children = [];
            foreach ($query['params']['get'] as $itemOrObject){
                if ($itemOrObject === 'items'){
                    $children = array_merge($children, $this->childrenItems($where, $object));
                }else if ($itemOrObject === 'objects'){
                    $children = array_merge($children, $this->childrenObjects($where, isset($object) ? [$object] : []));
                }
            }
            return ['items' => $children];
        }
    }

    function getPath($query){
        $itemsInPath = array_reverse($this->model->itemColPath(SUtl::$tukosModel->getOne(['where' => ['id' => $query['id']], 'cols' => ['id', 'object', 'name', 'parentid']]), 'parentid'));
        foreach($itemsInPath as &$item){
        	$item['object'] = $this->view->tr($item['object']);
        }
        return ['path' => $itemsInPath];
    }

}
?>
