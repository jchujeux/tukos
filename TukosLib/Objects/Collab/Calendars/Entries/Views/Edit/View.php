<?php

namespace TukosLib\Objects\Collab\Calendars\entries\Views\Edit;

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
                'widgets' => ['id', 'parentid', 'name', 'startdatetime', 'duration', 'enddatetime', 'allday', 'periodicity', 'lateststartdatetime' , 'backgroundcolor', 'googlecalid']
            ],
            'rowcomments' => [
                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['10%', '50%', '40%']],      
                'widgets' => ['participants', 'comments', 'worksheet'],
            ]
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowbottom', 'rowacl'], $this->dataLayout['contents']));
    }
}
?>
