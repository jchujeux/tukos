<?php

namespace TukosLib\Objects\Actions\Noview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class Get extends AbstractAction{
    function response($query){
        return $this->controller->objectsStore->objectViewModel($this->controller, 'Noview', $query['params']['actionModel'])->get($query);
    }
}
?>
