<?php

namespace TukosLib\Objects\Admin\Users\Navigation\Views\Pane\Models;

use TukosLib\Objects\Views\Models\Get as ViewsGetModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;

class Get extends ViewsGetModel {
    function childrenItems($parentId, $objectName){
        $tukosModel = SUtl::$tukosModel;
        return $tukosModel->store->getAll([
            'cols'  => ['tukos.id', 'tukos.object', 'tukos.name', 'count(*)-1 as children'],
            'table' => SUtl::$tukosTableName,
            'join'  => ['inner', SUtl::$tukosTableName . ' as t0', 'tukos.id = t0.parentid or tukos.id = t0.id'], 
            'where' => ['tukos.parentid' => $parentId, 'tukos.object' => $objectName, 0 => ['col' => 'tukos.id', 'opr' => '>', 'values' => 0], 1 => ['col' => 't0.id', 'opr' => '>', 'values' => 0]],
            'groupBy' => ['tukos.id']
            ]
        );
    }

    function childrenObjects($parentId='0', $excludeObjects = []){
        $atts = ['where' => ['parentid' => $parentId], 'cols' => ['object', 'parentid', 'count(*) as children'], 'groupBy' => ['object']];
        if (!empty($excludeObjects)){
            $atts['where'][] = ['col' => 'object', 'opr' => 'NOT IN', 'values' => $excludeObjects];
        }
        return SUtl::$tukosModel->getAll($atts);
    }

    function get($query){
        if ($query['params']['get'] == 'getPaths'){
            return $this->getPaths($query);
        }else{
            $parentId = $query['storeatts']['where']['parentid'];
            $children = [];
            foreach ($query['params']['get'] as $itemOrObject){
                if ($itemOrObject === 'items'){
                    $children = array_merge($children, $this->childrenItems($parentId, $query['params']['object']));
                }else if ($itemOrObject === 'objects'){
                    $children = array_merge($children, $this->childrenObjects($parentId, isset($query['params']['object']) ? [$query['params']['object']] : []));
                }
            }
            return ['items' => $children];
        }
    }
        
    protected function objectPathtoNavigationPath($objectPath){
        $objectPath = array_reverse($objectPath);
        if ($objectPath[0]['id'] != 0){
            return false;// should never happen!
        }else{
            $idColId = 0;
            $parentObject = '';
            foreach ($objectPath as $pathItem){
                if ($parentObject !== $pathItem['object']){
                    $navigationPath[] = $pathItem['object'] . $idColId; 
                }
                $navigationPath[] = $pathItem['id']; 
                $idColId = $pathItem['id'];
                $parentObject = $pathItem['object'];
            }
            return $navigationPath;
        }
    }

    function getPaths($query){
        $query = array_merge($query, SUtl::$tukosModel->getOne(['table' => $query['object'], 'where' => ['id' => $query['id']], 'cols' => ['name', 'parentid']]));
        $objectPath = $this->model->itemColPath($query, 'parentid');
        $navigationPaths = ['paths' => [$this->objectPathtoNavigationPath($objectPath)]];
        return $navigationPaths;
    }

}
?>
