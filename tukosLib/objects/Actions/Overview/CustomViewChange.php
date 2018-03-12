<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\Overview\Tab;
use TukosLib\TukosFramework as Tfk;

class CustomViewChange extends Tab{
    function response($query){
        $valuesToSave = $this->dialogue->getValues();
        if (isset($valuesToSave['customviewid'])){
            $this->user->setCustomViewId($this->objectName, 'overview', $valuesToSave['customviewid']);
        }
        return parent::response($query);
    }
}
?>
