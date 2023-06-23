<?php

namespace TukosLib\Objects\Sports\Sessions\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;

class View extends EditView{

    public static function editDialogLayout(){
        return [
            'row1' => [
                'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                'widgets' => ['id', 'parentid', 'name', 'mode', 'sessionid', 'startdate', 'sport', 'sportsman', 'googleid']
            ],
            'row2' => [
                'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                'widgets' => ['duration', 'intensity', 'stress', 'distance', 'elevationgain', 'stravaid'],
            ],
            'row3' => [
                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                'widgets' => ['warmup', 'mainactivity', 'warmdown', 'warmupdetails', 'mainactivitydetails', 'warmdowndetails'],
            ],
            'row4' => [
                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                'contents' => [
                    'col1' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                        'contents' => [
                            'row' => [
                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
                                'widgets' => ['sensations', 'perceivedeffort', 'perceivedmechload', 'mood']
                            ]
                        ]
                    ],
                    'col2' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                        'contents' => [
                            'row' => [
                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
                                'widgets' => ['athletecomments', 'coachcomments']
                            ]
                        ]
                    ],
                ]
            ],
            'row5' => [
                'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
                'widgets' => ['sts', 'lts', 'tsb', 'progressivity', 'timemoving', 'avghr', 'avgpw', 'trimphr', 'trimppw', 'trimpavghr', 'trimpavgpw', 'avgcadence', 'mechload', 'hr95', 'h4time', 'h5time'],
            ],
        ];
    }
    function __construct($actionController){
        parent::__construct($actionController);
        $this->dataLayout['contents'] = array_merge(self::editDialogLayout(), Utl::getItems(['rowcomments', 'rowbottom', 'rowacl'], $this->dataLayout['contents']));
    }
}
?>
