<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;

use TukosLib\TukosFramework as Tfk;

class HasChanged extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->hasChangedViewModel  = $controller->objectsStore->objectViewModel($controller, 'Edit', 'GetHasChanged');
    }
    function response($query){
        return $this->hasChangedViewModel->respond($query);
    }
}
?>
