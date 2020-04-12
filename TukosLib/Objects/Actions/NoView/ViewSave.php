<?php

namespace TukosLib\Objects\Actions\NoView;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class ViewSave extends AbstractAction{
    function response($query){
        $valuesToSave = $this->dialogue->getValues();
        reset($valuesToSave);
        $view = key($valuesToSave);
        return $this->user->updateCCustomView($this->objectName, $view, $valuesToSave[$view]);
    }
}
?>
