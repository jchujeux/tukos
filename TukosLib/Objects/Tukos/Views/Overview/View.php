<?php

namespace TukosLib\Objects\Tukos\Views\Overview;

use TukosLib\Objects\Views\Overview\View as OverviewView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends OverviewView{

	function __construct($actionController){
		parent::__construct($actionController);
		
		$view = $this->view;
		$view->dataWidgets['overview']['atts']['edit']['object'] = 'tukos';
		$view->dataWidgets['overview']['atts']['edit']['storeArgs']['action'] = 'GridSearch';
		Utl::extractItems(['duplicate', 'modify', 'delete', 'edit', 'import', 'process', 'export'], $this->actionWidgets);
		$this->actionWidgets['id'] = ['type' => 'TextBox', 'atts' => ['placeHolder' => $view->tr('id') . '...', 'style' => ['width' => '3em']]];
        $this->actionWidgets['pattern'] = ['type' => 'TextBox', 'atts' => ['placeHolder' => $view->tr('pattern') . '...', 'style' => ['width' => '10em']]];
        $this->actionWidgets['contextid'] = Widgets::description(Utl::array_merge_recursive_replace($view->dataWidgets['contextid'], ['atts' => ['edit' => ['style' => ['maxWidth' => '8em']]]]));
        $this->actionWidgets['reset']['atts']['label'] = $view->tr('search now');
        $this->actionWidgets['reset']['atts']['serverAction'] = 'Search';
        $this->actionLayout = [
        		'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
        		'contents' => [
        				'actions' => [
        						'tableAtts' => ['cols' => 4, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $view->tr('For all items') . '<b>', 'spacing' => '0'],
        						'widgets' => ['id', 'pattern', 'contextid', 'reset'],
        				],
        				'feedback' => [
        						'tableAtts' => ['cols' => 2, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $view->tr('Feedback') . ':<b>', 'spacing' => '0'],
        						'widgets' => ['clearFeedback', 'feedback'],
        				],
        		],
        ];
        
        
	}	
}
?>
