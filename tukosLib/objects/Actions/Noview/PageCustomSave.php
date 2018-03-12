<?php

namespace TukosLib\Objects\Actions\Noview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class PageCustomSave extends AbstractAction{
    function response($query){
        return $this->user->updateUserInfo($this->dialogue->getValues());
    }
}
?>
