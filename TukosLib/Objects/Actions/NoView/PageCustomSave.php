<?php

namespace TukosLib\Objects\Actions\NoView;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class PageCustomSave extends AbstractAction{
    function response($query){
        return $this->user->updatePageCustom($this->dialogue->getValues(), $query['tukosOrUser']);
    }
}
?>
