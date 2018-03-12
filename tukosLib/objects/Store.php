<?php
/**
 *
 * Provide methods to deal with parent - child methods for object items
 */
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

    public function getItems($atts){
        $transformedGet = SUtl::transformGet($atts, $this->tableName);
        $items = $this->store->getAll($transformedGet);
        return $items;
    }

    public function updateItem($item, $atts){
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
        $tukosTable = $this->tukosModel->tableName();
        while ($idColId > 0){
            $item    = $this->store->getOne(['table' => $tukosTable, 'where' => ['id' => $idColId], 'cols' => ['name', 'object']]);
            $path[]  = ['id' => $idColId, 'name' => $item['name'], 'object' => $item['object']];
            $idColId = $this->store->getOne(['table' => $item['object'], 'where' => ['id' => $idColId], 'cols' => [$idCol]])[$idCol];
        }
        $path[] = ['id' => '0', 'name' => '', 'object' => ''];
        return $path;    
    }
   
    public function itemColPath($item, $idCol){/* $item = ['id' => $id, 'name' => $name, $idCol => $idColId, ...]*/
        return array_merge([['id' => $item['id'], 'name' => $item['name'], 'object' => $item['object']]], $this->idColIdPath($item[$idCol], $idCol));
    }
}
?>
