<?php

namespace TukosLib\Objects\Collab\Calendars\entries\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController);

        $this->dataLayout   = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false,  'content' => ''],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
                    'widgets' => ['id', 'parentid', 'name', 'startdatetime', 'duration', 'enddatetime', 'allday', 'periodicity', 'lateststartdatetime' , 'backgroundcolor', 'googlecalid']
                ],
                'row2' => [
                    'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['10%', '50%', '40%']],      
                    'widgets' => ['participants', 'comments', 'worksheet'],
                ],
                'row3' => [
                     'tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 60],
                     'widgets' => ['permission', 'grade', 'contextid', 'updated', 'updator', 'created', 'creator']
                ],
                'row4' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0',],
                    'widgets' =>  (in_array('history', $this->view->model->allCols) ? ['history'] : []),
                ],
            ]
        ];

    }
}
?>
