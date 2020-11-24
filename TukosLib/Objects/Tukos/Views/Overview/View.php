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
		$tr = $view->tr;
		$view->dataWidgets['overview']['atts']['edit']['object'] = 'tukos';
		$view->dataWidgets['overview']['atts']['edit']['storeArgs']['action'] = 'GridSearch';
		Utl::extractItems(['duplicate', 'modify', 'delete', 'edit', 'import', 'process', 'export'], $this->actionWidgets);
		$this->actionWidgets['id'] = ['type' => 'TextBox', 'atts' => ['placeHolder' => $tr('id') . '...', 'style' => ['width' => '3em']]];
        $this->actionWidgets['pattern'] = ['type' => 'TextBox', 'atts' => Widgets::complete(['title' => $tr('pattern'), 'style' => ['width' => '10em']])];
        $this->actionWidgets['contextid'] = Widgets::description(Utl::array_merge_recursive_replace($view->dataWidgets['contextid'], ['atts' => ['edit' => ['style' => ['maxWidth' => '8em']]]]));
        //$this->actionWidgets['eliminate'] = ['type' => 'CheckBox', 'atts' => Widgets::complete(['title' => "{$tr('Eliminatelabel')} "])];
        $this->actionWidgets['itemstypestosearch'] = ['type' => 'StoreSelect', 'atts' => Widgets::complete(['title' => "{$tr('itemstypestosearch')}", 'storeArgs' => ['data' => Utl::idsNamesStore(['activeitems', 'eliminateditems'], $tr)],
            'value' => 'activeitems', 'onChangeLocalAction' => $this->itemsTypesToSearchLocalAction()])];
        $this->actionWidgets['restore'] =  ['type' => 'OverviewAction', 'atts' => ['label' => $this->view->tr('Restore'), 'hidden' => true, 'grid' => 'overview', 'serverAction' => 'Restore']];
        $this->actionWidgets['reset']['atts']['label'] = $tr('search now');
        $this->actionWidgets['reset']['atts']['serverAction'] = 'Search';
        $this->actionWidgets['feedback']['atts']['cols'] = 50;
        $this->actionLayout = [
        		'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '1'],
        		'contents' => [
        				'actions' => [
        				    'tableAtts' => ['cols' => 3, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $tr('For all items') . '<b>', 'spacing' => '0'],
        				    'contents' => [
        				        'col1' => [
        				            'tableAtts' => ['cols' => 3, 'customClass' => 'actionTable', 'showLabels' => false, 'spacing' => '0'],
        				            'widgets' => ['id', 'pattern', 'contextid']
        				        ],
        				        'col2' => [
        				            'tableAtts' => ['cols' => 1, 'customClass' => 'actionTable', 'showLabels' => true, 'spacing' => '0'],
        				            'widgets' => ['itemstypestosearch']
        				        ],
        				        'col3' => [
        				            'tableAtts' => ['cols' => 1, 'customClass' => 'actionTable', 'showLabels' => false, 'spacing' => '0'],
        				            'widgets' => ['reset']
        				        ]
        				    ],
        				],
        		    'selection' => [
        		        'tableAtts' => ['cols' => 1, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('For selected items') . '<b>', 'spacing' => '0', 
        		            'widgetCellStyle' => ['width' => '130px', 'text-align' => 'center']],
        		        'widgets' => ['restore']
        		    ],
        		    'feedback' => [
        						'tableAtts' => ['cols' => 2, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $tr('Feedback') . ':<b>', 'spacing' => '0'],
        						'widgets' => ['clearFeedback', 'feedback'],
        				],
        		],
        ];
	}
	function itemsTypesToSearchLocalAction(){
	    return ['itemstypestosearch' => ['localActionStatus' => <<<EOT
var form = sWidget.form, restoreWidget = form.getWidget('restore');
restoreWidget.set('hidden', newValue === 'eliminateditems' ? false : true);
form.resize();
return true;
EOT
	    ]];
	}
}
?>
