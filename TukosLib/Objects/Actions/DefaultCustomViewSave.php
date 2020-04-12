<?php

namespace TukosLib\Objects\Actions;


trait DefaultCustomViewSave{
    function response($query){
        $toSave = $this->dialogue->getValues();
        $this->user->updateCustomView($this->objectName, $this->request['view'], $this->paneMode, $toSave['customization'], $toSave['tukosOrUser']);
        return parent::response($query);
    }
}
?>
