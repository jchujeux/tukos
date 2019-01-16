<?php

namespace TukosLib\Objects\Views\Overview;

use TukosLib\Objects\Views\Overview\View as OverviewView;
use TukosLib\Objects\Views\LocalActions;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class QuestionnaireView extends OverviewView{
	
	use LocalActions;
	
	function __construct($actionController){
		parent::__construct($actionController);
		$this->actionWidgets['questionnaires'] =  ['type' => 'OverviewAction',     'atts' => ['label' => $this->view->tr('Questionnaires'), 'grid' => 'overview', 'serverAction' => 'Reset', 'queryParams' => ['process' => 'questionnaires']]];
		$this->actionLayout['contents']['actions']['widgets'][] = 'questionnaires';
       	$this->actionWidgets['questionnaires']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'widgetsDescription' => [
                   'googlesheetid' => Widgets::textBox(Widgets::complete(['title' => $this->view->tr('Googlesheetid'), 'style' => ['width' => '30em'], 'onWatchLocalAction' =>  $this->watchLocalAction('googlesheetid')])),
                	'template' => Widgets::objectSelect(Widgets::complete([
                		'title' => $this->view->tr('template'), 'object' => $this->model->objectName, 'dropdownFilters' => ['grade' => 'TEMPLATE'], 'onWatchLocalAction' =>  $this->watchLocalAction('template')
                	])),
                    'process' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('Getnewquestionnaires'), 'onClickAction' => 
                        "var pane = this.pane, attachedWidget = pane.attachedWidget, resetAction = lang.hitch(attachedWidget, attachedWidget.resetAction),\n" .
                    	"    options = {googlesheetid: pane.valueOf('googlesheetid'), template: pane.valueOf('template')};\n" .
                    	"resetAction(options);\n" .
                    	"pane.close();"
                    ]],
                    'cancel' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('cancel'), 'onClickAction' => "this.pane.close();"]],
                ],
                'layout' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                    'contents' => [
                        'row1' => [
                            'tableAtts' =>['cols' =>1,  'customClass' => 'labelsAndValues', 'showLabels' => true],
                            'widgets' => ['googlesheetid', 'template'],
                        ],
                        'row2' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                                    'widgets' => ['process', 'cancel'],
                        ],
                    ],
                ],                          
            ]
        ];
	}
/*
    function watchLocalAction($att){
        return ['value' => [
                $att  => ['localActionStatus' => [
                        'action' => "sWidget.pane.form.addCustom({value: newValue}, ['widgetsDescription', sWidget.pane.attachedWidget.widgetName, 'atts', 'dialogDescription', 'paneDescription', 'widgetsDescription', '" . $att . "', 'atts']);return true;",
                ]],
        ]];
    }
*/
}
?>