<?php

namespace TukosLib\Objects\Physio\PersoTrack\DailyAssesments\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;


class View extends EditView{
    
    function __construct($actionController){
        parent::__construct($actionController);
        
        $customContents = [
            'row1' => [
                'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
                'widgets' => ['id', 'parentid', 'startdate']
            ],
            'row2' => [
                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'widgetWidths' => ['auto', '100%']],
                'contents' => [
                    'col1' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                        'widgets' => ['exercises', 'physiopersosessions']
                    ],
                    'col2' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                        'contents' => [
                            'row1' => [
                                'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                'widgets' => ['name']
                            ],
                            'row2' => [
                                'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                'widgets' => ['painduring', 'painafter', 'painnextday']
                            ],
                            'row3' => [
                                'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                'widgets' => ['mood', 'fatigue']
                            ],
                            'row4' => [
                                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                'widgets' => ['otherexceptional', 'comments']
                            ],
                        ]
                    ]
                ]
            ],
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowbottom', 'rowacl'], $this->dataLayout['contents']));
    }
}
?>
