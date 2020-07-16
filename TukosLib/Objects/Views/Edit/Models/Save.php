<?php

namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Objects\Views\Models\Save as SaveModel;
use TukosLib\Objects\Views\Edit\Models\SubObjectsSave;


use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Save extends SaveModel {

    function save($query, $ignoreNoChange = false){
        if (!empty($query['params'])){
            $this->modelSaveOneNew       = (empty($query['params']['saveOneNew'])       ? $this->modelSaveOneNew      : $query['params']['saveOneNew']);
            $this->modelSaveOneExisting  = (empty($query['params']['saveOneExisting'])  ? $this->modelSaveOneExisting : $query['params']['saveOneExisting']);
        }
        $valuesToSave = $this->dialogue->getValues();
        //$valuesToSave = Utl::getItem('values', $valuesToSave, $valuesToSave);
        
        $subObjectsToSave = SubObjectsSave::extractValues($this->view->subObjects, $valuesToSave);
        if (empty($valuesToSave['id']) && !empty($query['id'])){
            $valuesToSave['id'] = $query['id'];
        }
        $idSaved = $this->saveOne($valuesToSave, 'editToObj');
        if ($subObjectsToSave){
            $subObjectsOutcome = SubObjectsSave::save($this, $subObjectsToSave, isset($valuesToSave['id']) ? null : $idSaved, $idSaved);
        }
        if ($idSaved){
            return $idSaved;
        }else if(!empty($subObjectsOutcome['saved']) || !empty($subObjectsOutcome['deleted'])){
            return $valuesToSave['id'];
        }else{
        	return $ignoreNoChange ? $valuesToSave['id'] :  $idSaved;
        }
    }

}
?>
