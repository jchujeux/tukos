<?php

namespace TukosLib\Objects\Collab\Documents\Actions\NoView;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class Upload extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->actionModel  = $controller->objectsStore->objectViewModel($controller, 'NoView', 'Upload');
        //$this->getModel     = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Get');
    }
    function response($query){/* $query is ignored, provided for consistency with other controller actions*/
        return $this->actionModel->upload();
    }
}
?>
