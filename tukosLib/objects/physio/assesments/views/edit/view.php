<?php

namespace TukosLib\Objects\Physio\Assesments\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController);

        $this->dataLayout   = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false,  'content' => '', 'orientation' => 'vert'],
            'contents' => [
                'row1' => [
                     'tableAtts' => ['cols' => 8, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 60],
                     'widgets' => ['id', 'parentid', 'patient', 'prescriptor', 'name', 'physiotherapist', 'assesmenttype', 'assesmentdate']
                ],
                'row2' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                    'widgets' => ['assesment'],
                ],
                'row3' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                    'widgets' => ['worksheet', 'comments'],
                ],
                'row4' => [
                     'tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 60],
                     'widgets' => ['permission', 'grade', 'contextid', 'updated', 'updator', 'created', 'creator']
                ],
            ]
        ];
        if ($this->view->dataWidgets['configstatus']){
            $this->dataLayout['contents']['row4']['widgets'][] = 'configstatus';
        }
         $this->actionWidgets['export']['atts']['dialogDescription'] = [
            //'title'   => $this->view->tr('weeklyprogram'),
            'paneDescription' => [
                'widgetsDescription' => [
                   'template' => ['atts' => ['value' => '${@assesment}']]
                ]
            ]
        ];

    }
}
?>
