<?php

namespace TukosLib\Objects\Itm\Itsm\incidents\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController);
        $this->dataLayout['contents']['rowcomments'] = [
            'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['30%', '70%', '30%']],
            'widgets' => ['statushistory', 'comments', 'worksheet']
        ];
    }
}
?>
