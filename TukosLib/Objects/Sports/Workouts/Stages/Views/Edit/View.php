<?php

namespace TukosLib\Objects\Sports\Workouts\Stages\Views\Edit;

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
        		'widgets' => ['id', 'parentid', 'name', 'stagetype', 'duration', 'intensity', 'stress', 'sport']
        	],
        	'row2' => [
        		'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false,  'content' => '', 'widgetWidths' => ['60%', '40%']/*, 'style' => 'height: 100%; width: 100%'*/],
	            'contents' => [
	            	'col1' => [
	            		'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
	            		'contents' => [
	            			'row3' => [
	            					'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 
	            					'widgets' => [ 'summary', 'details']
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
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowcomments', 'rowbottom', 'rowacl'], $this->dataLayout['contents']));
    }
}
?>
