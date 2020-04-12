<?php

namespace TukosLib\Objects\Actions\NoView;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class ModuleContextSave extends AbstractAction{
    function response($query){
        $valuesToSave = $this->dialogue->getValues();
        return $this->user->updateModuleContext($query['module'], $valuesToSave);
        //return [];
    }
}
?>
