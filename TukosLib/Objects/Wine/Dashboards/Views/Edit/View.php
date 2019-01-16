<?php

namespace TukosLib\Objects\Wine\Dashboards\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController); 

        $this->dataLayout['contents']['row1']['widgets'] = ['id', 'parentid', 'name', 'inventorydate', 'quantity', 'count'];
        $this->dataLayout['contents']['pieCharts'] = ['tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'orientation' => 'vert'], 'widgets' => ['quantityperregion', 'quantitypercategory', 'quantitypercolor', 'quantitypersugar']];
        $this->dataLayout['contents']['colCharts'] = ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert'], 'widgets' => ['quantitypervintage']];

        $this->actionLayout['contents']['actions']['widgets'][] = 'process';
    }
}
?>
