<?php

namespace TukosLib\Objects\Sports\Sessions\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\Sports\GoldenCheetah as GC;


class View extends EditView{

    /*const customContents = [
        'row1' => [
            'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
            'widgets' => ['id', 'parentid', 'name', 'sessionid', 'startdate', 'duration', 'intensity', 'stress', 'sport', 'sportsman', 'googleid']
        ],
        'row1b' => [
            'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
            'widgets' => ['mode', 'distance', 'elevationgain', 'athletecomments', 'coachcomments', 'sensations', 'perceivedeffort', 'mood', 'sts', 'lts', 'tsb', 'timemoving', 'avghr', 'avgpw', 'trimphr', 'trimppw', 'trimpavghr', 'trimpavgpw', 'mechload', 'hr95', 'h4time', 'h5time', 'stravaid']
        ],
        'row2' => [
            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false,  'content' => '', 'widgetWidths' => ['60%', '40%']],
            'contents' => [
                'col1' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                    'contents' => [
                        'row3' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                            'widgets' => [ 'warmup', 'warmupdetails', 'mainactivity', 'mainactivitydetails', 'warmdown', 'warmdowndetails']
                        ],
                    ]
                ],
                'col2' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                    'contents' => [
                        'row0' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
                            'widgets' => ['warmuptemplates', 'mainactivitytemplates', 'warmdowntemplates', 'varioustemplates'],
                        ],
                        'row1' => [
                            'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
                            'widgets' => ['level1', 'level2', 'level3']
                        ],
                        'row2' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
                            'widgets' => ['exercisestemplates'],
                        ],
                    ],
                ],
            ]
        ]
    ];*/
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
                                'widgets' => ['sensations', 'perceivedeffort', 'mood']
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
                'widgets' => ['sts', 'lts', 'tsb', 'timemoving', 'avghr', 'avgpw', 'trimphr', 'trimppw', 'trimpavghr', 'trimpavgpw', 'mechload', 'hr95', 'h4time', 'h5time'],
            ],
        ];
    }
    function __construct($actionController){
        parent::__construct($actionController);
        $this->dataLayout['contents'] = array_merge(self::editDialogLayout(), Utl::getItems(['rowcomments', 'rowbottom', 'rowacl'], $this->dataLayout['contents']));
    }
}
?>
