<?php

namespace TukosLib\Objects\Physio\Protocols\Views\Edit;

use TukosLib\Objects\Collab\Calendars\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController);

        $this->dataLayout['contents']['row2']['contents']['col3']['widgets'][] = 'sessionsentries';
    }
}
?>
