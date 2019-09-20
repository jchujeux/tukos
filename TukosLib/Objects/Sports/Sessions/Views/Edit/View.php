<?php

namespace TukosLib\Objects\Sports\Sessions\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController);

        $this->dataLayout   = [
          	'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
                    'widgets' => ['id', 'parentid', 'name', 'sessionid', 'startdate', 'duration', 'intensity', 'stress', 'sport', 'sportsman', 'difficulty', 'googleid']
                ],
                'row1b' => [
                    'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
                    'widgets' => ['mode', 'distance', 'elevationgain', 'feeling', 'athletecomments', 'athleteweeklycomments', 'coachcomments', 'coachweeklycomments', 'sensations', 'perceivedeffort', 'mood']
                ],
                'row2' => [
            		'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false,  'content' => '', 'widgetWidths' => ['60%', '40%']/*, 'style' => 'height: 100%; width: 100%'*/],
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
		            				'widgets' => ['level1filter', 'level2filter', 'level3filter']
		            			],
		            			'row2' => [
		            				'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
		            				'widgets' => ['exercisestemplates'],
		            			],
		            		],
		            	],
		            ]
    			],
            	'row3' => [
            		'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false,  'content' => ''/*, 'style' => 'height: 100%; width: 100%'*/],
		            'contents' => [
		            	'row1' => [
		            		'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'/*, 'widgetWidths' => ['30%', '70%']*/],
		            		'widgets' => ['comments'],
		            	],
		            	'row4' => [
		            		'tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 60],
		            		'widgets' => ['permission', 'grade', 'contextid', 'updated', 'updator', 'created', 'creator']
		            	],
		            ]
		        ]
            ]
        ];
    }
}
?>
