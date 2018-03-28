<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\Edit\Tab;
use TukosLib\TukosFramework as Tfk;

class TabRefresh extends Tab{
    function response($query){
        $valuesToSave = $this->dialogue->getValues();
        if (isset($valuesToSave['customviewid'])){
            $this->user->setCustomViewId($this->objectName, 'edit', $this->paneMode, $valuesToSave['customviewid']);
        }
    }
}
?>
