<?php

namespace TukosLib\Objects\Views\NoView\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class GetItems extends AbstractViewModel{

   function get($query){
        $itemsProperties = $this->dialogue->getValues();
        $result = [];
        $objectNames = Utl::toAssociative(SUtl::$store->getAll(['table' => SUtl::$tukosTableName, 'where' => [['col' => 'id', 'opr' => 'IN', 'values' => array_keys($itemsProperties)]], 'cols' => ['id', 'object']]), 'id');
        foreach ($itemsProperties as $id => $properties){
            try{
                $objectName = $objectNames[$id]['object'];
                $values = Tfk::$registry->get('objectsStore')->objectModel($objectName)->getOne(['where' => ['id' => $id], 'cols' => $properties], [], null, '');
                $view = Tfk::$registry->get('objectsStore')->objectView($objectName);
                $result[$id] = $this->convert($values, $view->dataWidgets, 'objToEdit', false, false, true);
            }catch(\Exception $e){
                Feedback::add(utl::sentence(array_merge(['couldnotretrieveapropertyamong', '['], $properties, [']', 'for', $objectNames[$id]['object'], 'item', $id]), $this->view->tr));
                Feedback::add($e->getMessage());
            }            
        } 
        return ['data' => $result];
    }
}
?>
