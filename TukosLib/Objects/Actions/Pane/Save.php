<?php

namespace TukosLib\Objects\Actions\Pane;

use TukosLib\Objects\Actions\Pane\AbstractAction;

use TukosLib\TukosFramework as Tfk;

class Save extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->actionModel  = $controller->objectsStore->objectViewModel($controller, 'Pane', 'Save');
    }
    function response($query){/* $query is ignored, provided for consistency with other controller actions*/
        $savedId = $this->actionModel->save();
        return [];
    }
}
?>
