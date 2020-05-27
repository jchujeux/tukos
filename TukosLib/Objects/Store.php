<?php
namespace TukosLib\Objects;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;

trait Store {

    public function getItem($atts){
        $transformedGet = SUtl::transformGet($atts, $this->tableName);
        $item = $this->store->getOne($transformedGet);
        return $item;
    }

    public function getItems($atts, $processLargeCols){
        $fieldsMaxSize = 0;
        $transformedGet = SUtl::transformGet($atts, $this->tableName, 
            $processLargeCols ? (!empty($fieldsMaxSize = $this->user->fieldsMaxSize()) ? ($atts['cols'] === '*' ? $this->maxSizeCols : array_intersect($atts['cols'], $this->maxSizeCols)) : []) : [], $fieldsMaxSize);
        $items = $this->store->getAll($transformedGet);
        return $items;
    }

    public function updateItems($item, $atts){
    	$item = array_intersect_key($item, array_flip($this->allCols));
    	$transformedAtts = SUtl::transformUpdate($item, $atts, $this->tableName);
        return $this->store->update($item, $transformedAtts);
    }

    public function insertItem($item){
    	$item = array_intersect_key($item, array_flip($this->allCols));
    	$newId = $item['id'];
        if (!empty($this->objectCols)){
            $objectTableCols = array_intersect(array_keys($item), $this->objectCols);
            if (!empty($objectTableCols)){
                $objectTableValues = Utl::extractItems($objectTableCols, $item);
            }
            $objectTableValues['id'] = $newId;
            $this->store->insert($objectTableValues, ['table' => $this->tableName]);
        }
        $atts['table'] = $this->tukosModel->tableName();
        $item['object'] = $this->tableName;
        return $this->store->insert($item, $atts);
    }
    public function addItemIdCols($item){
        SUtl::addItemIdCols($item, array_merge($this->idCols, ['id']));
        foreach ($this->gridsIdCols as $col => $idCols){
            if (isset($item[$col]) && is_array($item[$col])){
                SUtl::addItemsIdCols($item[$col], $idCols);
            }
        }
    }

    public function addItemsIdCols($items){
        SUtl::addItemsIdCols($items, array_merge($this->idCols, ['id']));
    }
    
    protected function idColIdPath($idColId, $idCol){
        $path = [];
        while ($idColId > 0){
            $item    = SUtl::$tukosModel->getOne(['where' => ['id' => $idColId], 'cols' => ['id', 'name', 'object', $idColId]]);
            $idColId = Utl::extractItem($idColId, $item);
            $path[]  = $item;
        }
        $path[] = ['id' => '0', 'name' => '', 'object' => ''];
        return $path;    
    }
   
    public function itemColPath($item, $idCol){
        $path = [];
    	while(!empty($item)){
        	$idColId = Utl::extractItem($idCol, $item);
        	$path[] = $item;
        	$item = $idColId > 0 ? SUtl::$tukosModel->getOne(['where' => ['id' => $idColId], 'cols' => ['id', 'name', 'object', $idCol]]) : [];
        }
        $path[] = ['id' => '0', 'name' => '', 'object' => ''];
        return $path;
        //return array_merge([['id' => $item['id'], 'name' => $item['name'], 'object' => $item['object']]], $this->idColIdPath($item[$idCol], $idCol));
    }
}
?>
