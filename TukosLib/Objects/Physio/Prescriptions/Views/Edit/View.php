<?php

namespace TukosLib\Objects\Physio\Prescriptions\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController);

        $customContents = [
            'row1' => [
                 'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 100],
                 'widgets' => ['id', 'parentid', 'prescriptor', 'name', 'prescriptiondate', 'quantitative', 'nbofsessions',  'physiobefore']
            ],
            'row2' => [
                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => false, /*'label' => $this->view->tr('Socioadmin'), */'orientation' => 'vert', 'labelWidth' => 75,],
                'contents' => [
                    'col1' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 'widgets' => ['prescription']],
                    'col2' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 'widgets' => ['otherexams']],
                    'col3' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 'widgets' => ['medicalindic']],
                ]
            ]
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowcomments', 'rowsubobjects', 'rowbottom', 'rowacl'], $this->dataLayout['contents']));
    }
}
?>
