<?php

namespace TukosLib\Objects\Sports\Exercises\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController);

        $customContents = [
            'row1' => [
                'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
                'widgets' => ['id', 'parentid', 'name', 'level1', 'level2', 'level3']
            ],
            'row2' => [
                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],      
                'widgets' => ['visual', 'protocol', 'comments'],
            ]
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowbottom', 'rowacl'], $this->dataLayout['contents']));
    }
}
?>
