<?php

namespace TukosLib\Objects\Actions\Pane;

use TukosLib\Objects\Actions\AbstractAction as ParentAction;
use TukosLib\Objects\Views\Pane\View as PaneView;
use TukosLib\TukosFramework as Tfk;

class AbstractAction extends ParentAction {
    function __construct($controller){
        parent::__construct($controller);
        $this->actionView = new PaneView($this);
    }
}
?>
