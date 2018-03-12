<?php

namespace TukosLib\Objects\Actions;


trait DefaultCustomViewIdSave{
    function response($query){
        $valuesToSave = $this->dialogue->getValues();
        $newCustomViewId = $valuesToSave['customviewid'];
        if (isset($this->controller->customViewId)){
           	$this->user->updateUserInfo($valuesToSave);
            $this->controller->customViewId = $newCustomViewId;
        }else{
            $this->user->setCustomViewId($this->objectName, $this->request['view'], $newCustomViewId);
        }
        return parent::response($query);
    }
}
?>
