<?php

namespace TukosLib\Objects\Actions\Noview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class RestSelect extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->actionModel  = $controller->objectsStore->objectViewModel($controller, 'Noview', 'RestSelect');
    }
    function response($query){
        return $this->actionModel->get($query);
    }
}
?>
