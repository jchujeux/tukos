<?php

namespace TukosLib\Objects\Itm\Itsm\SvcDescriptions\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController);
        $this->dataLayout['contents']['row2'] = [
            'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['20%', '8%', '50%', '30%']],
            'widgets' => ['incidentswf', 'incidentssla', 'comments', 'worksheet']
        ];
        unset($this->dataLayout['contents']['row1']['widgets'][array_search('incidentswf', $this->dataLayout['contents']['row1']['widgets'])]);
        unset($this->dataLayout['contents']['row1']['widgets'][array_search('incidentssla', $this->dataLayout['contents']['row1']['widgets'])]);
    }
}
?>
