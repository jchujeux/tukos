<?php

namespace TukosLib\Objects\Actions\Noview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class ViewSave extends AbstractAction{
    function response($query){
        $valuesToSave = $this->dialogue->getValues();
        reset($valuesToSave);
        $view = key($valuesToSave);
        return $this->user->updateCustomView($this->objectName, $view, $valuesToSave[$view]);
    }
}
?>
