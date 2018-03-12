<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class SubObject extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->SubObjectViewModel  = $controller->objectsStore->objectViewModel($controller, 'Edit', 'SubObject');
    }
    function response($query){
        $action = Utl::extractItem('action', $query);
        return $this->SubObjectViewModel->$action($query);
    }
}
?>
