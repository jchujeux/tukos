<?php

namespace TukosLib\Objects\Collab\Calendars\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

	function __construct($actionController){
		parent::__construct($actionController);

		$customContents = [
			'row1' => [
				'tableAtts' => ['cols' => 8, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
				'widgets' => ['id', 'parentid', 'name', 'displayeddate', 'periodstart', 'periodend', 'weeksbefore', 'weeksafter']
			],
			'row2' => [
				'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['30%', '40%', '30%']],	  
				'contents' => [			  
					'col2' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 'widgets' => [ 'calendar']],
					'col3' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 'widgets' => [ 'sources', 'comments', 'templates', 'sessionsentries']],
				]
			],
		    'row3' => [
		        'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
		        'widgets' => ['calendarsentries'],
		    ]
		];
		$this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowbottom', 'rowacl'], $this->dataLayout['contents']));
		$this->onOpenAction = $this->view->gridOpenAction($this->view->gridWidgetName);
	}	
}
?>
