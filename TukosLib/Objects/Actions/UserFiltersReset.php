<?php

namespace TukosLib\Objects\Actions;


trait UserFiltersReset{
    function response($query){
        $this->user->updateCustomView($this->objectName, $this->request['view'], $this->paneMode, $this->dialogue->getValues());
        return parent::response($query);
    }
}
?>
