<?php

namespace TukosLib\Objects\Collab\Documents\Actions\NoView;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class Download extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->actionModel  = $controller->objectsStore->objectViewModel($controller, 'NoView', 'Download');
    }
    function response($query){/* $query is ignored, provided for consistency with other controller actions*/
        return $this->actionModel->download($query);
    }
}
?>
