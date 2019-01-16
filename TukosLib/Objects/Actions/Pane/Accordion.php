<?php

namespace TukosLib\Objects\Actions\Pane;

use TukosLib\Objects\Actions\Pane\AbstractAction;
//use TukosLib\Objects\Views\Pane\Models\Get as PaneGetModel;
use TukosLib\TukosFramework as Tfk;

class Accordion extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        //$this->actionModel  = new PaneGetModel($this);
    }
    function response($query){
        return $this->actionView->content();
    }
}
?>
