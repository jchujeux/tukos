<?php

namespace TukosLib\Objects\Actions;


trait DefaultCustomViewIdSave{
    function response($query){
        $valuesToSave = $this->dialogue->getValues();
        $this->user->setCustomViewId($this->objectName, $this->request['view'], $this->paneMode, $valuesToSave['customviewid']);
        return parent::response($query);
    }
}
?>
