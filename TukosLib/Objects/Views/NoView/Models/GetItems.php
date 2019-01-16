<?php

namespace TukosLib\Objects\Views\NoView\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class GetItems extends AbstractViewModel{

   function get($query){
        $itemsAndObjects = $this->dialogue->getValues();
        $itemsProperties = $itemsAndObjects['items'];
        $objectNames = $itemsAndObjects['objects'];
        $result = [];
        foreach ($itemsProperties as $id => $properties){
            try{
                $values = Tfk::$registry->get('objectsStore')->objectModel($objectNames[$id])->getOne(['where' => ['id' => $id], 'cols' => $properties]);
                $view = Tfk::$registry->get('objectsStore')->objectView($objectNames[$id]);
                $result[$id] = $this->convert($values, $view->dataWidgets, 'objToEdit', false, false, true);
            }catch(\Exception $e){
                Feedback::add(utl::sentence(array_merge(['couldnotretrieveapropertyamong', '['], $properties, [']', 'for', $objectNames[$id], 'item', $id]), $this->view->tr));
                Feedback::add($e->getMessage());
            }            
        } 
        return ['data' => $result];
    }
}
?>
