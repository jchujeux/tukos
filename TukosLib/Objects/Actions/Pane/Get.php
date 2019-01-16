<?php

namespace TukosLib\Objects\Actions\Pane;

use TukosLib\Objects\Actions\Pane\AbstractAction;

use TukosLib\TukosFramework as Tfk;

class Get extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->actionModel  = $controller->objectsStore->objectViewModel($controller, 'Pane', 'Get');
    }
    function response($query){
        return $this->actionModel->get($query);
    }
}
?>
