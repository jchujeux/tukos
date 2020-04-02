<?php

namespace TukosLib\Objects\Physio\Assesments\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController);

        $customContents = [
            'row1' => [
                 'tableAtts' => ['cols' => 8, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 60],
                 'widgets' => ['id', 'parentid', 'patient', 'prescriptor', 'name', 'physiotherapist', 'assesmenttype', 'assesmentdate']
            ],
            'row2' => [
                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                'widgets' => ['assesment'],
            ]
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowcomments', 'rowbottom', 'rowacl'], $this->dataLayout['contents']));
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
