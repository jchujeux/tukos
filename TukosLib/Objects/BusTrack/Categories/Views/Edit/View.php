<?php

namespace TukosLib\Objects\BusTrack\Categories\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController); 
        $this->dataLayout['contents']['row1'] = [
            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'widgetWidths' => ['75%', '25%']],
                    'contents' => [
                        'col1' => [
                            'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 100],
                            'widgets' => ['id', 'parentid', 'name', 'vatfree', 'vatrate']
                        ],
                        'col2' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                            'widgets' => ['criteria']
                        ]
                    ]
                ]
            ]
        ];
    }
}
?>
