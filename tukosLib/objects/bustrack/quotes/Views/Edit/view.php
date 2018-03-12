<?php

namespace TukosLib\Objects\BusTrack\Quotes\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Objects\Views\LocalActions;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

	use LocalActions;
	
	function __construct($actionController){
       parent::__construct($actionController);

        $this->dataLayout   = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false,  'content' => ''],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 9, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 80],
                    'widgets' => ['id', 'parentid', 'name', 'reference', 'quotedate', 'status']
                ],
                'row2' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['60%', '40%']],      
                    'contents' => [              
                        'col1' => [
                        	'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                       		'contents' => [
                       			'row1' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 'widgets' => [ 'items']],
                       			'row2' => ['tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true], 'widgets' => [ 'discountpc', 'discountwt', 'downpay', 'pricewot', 'pricewt']],
                       		]
                       	],
                        'col2' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 
                            'widgets' => ['catalog'],
                       ],
                    ]
                ],
                'row3' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
                    'widgets' => ['comments'],
                ],
                'row4' => [
                     'tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 60],
                     'widgets' => ['permission', 'grade', 'contextid', 'updated', 'updator', 'created', 'creator']
                ],
            ]
        ];
/*
		$this->onOpenAction =
			"var widget = this.getWidget('discountwt'), discountwt = widget.get('value');\n" .
			"if (!isNaN(discountwt) && discountwt != null && discountwt > 0){\n" .
				"this.setValueOf('discountpc', discountwt / (this.valueOf('pricewt') + discountwt));\n" . 
			"}"
		;
*/        
        $this->actionWidgets['export']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'widgetsDescription' => [
                	'template' => ['atts' => ['value' => Utl::utf8('<br>${§quotetable}<br>${$@comments}')]],
                	'referenceprefix' => Widgets::textBox(['label' => $this->view->tr('referenceprefix'), 'style' => ['width' => '6em'], 'onWatchLocalAction' => $this->watchLocalAction('referenceprefix')]),
                	'daysvalid' => Widgets::tukosNumberBox(['label' => $this->view->tr('daysvalid'), 'style' => ['width' => '4em'], 'onWatchLocalAction' => $this->watchLocalAction('daysvalid')]),
                   	'rowId' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showrowId'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('rowId')])),
                   	'catalogid' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showcatalogid'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('catalogid')])),
                   	'comments' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showcomments'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('comments')])),
                		'update' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('prepare'), 'hidden' => true, 'onClickAction' => 
                        "this.pane.serverAction(" .
                        	"{action: 'process', query: {id: true, params: " . json_encode(['process' => 'quoteTable', 'noget' => true]) . "}}, " .
                        	"{includeWidgets: ['daysvalid', 'rowId', 'catalogid', 'comments'], " .
                    		  "includeFormWidgets: ['id', 'name', 'parentid', 'reference', 'quotedate', 'items', 'discountwt', 'pricewot', 'pricewt', 'downpay']}).then(lang.hitch(this, function(){\n" .
                            "this.pane.previewContent();" .
                            "this.set('hidden', true);" .
                        "}));"  
                    ]],
                    'quotetable'  => Widgets::editor(Widgets::complete(['title' => $this->view->tr('quotetable'), 'hidden' => true])),
                ],
                'layout' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                    'contents' => [
                        'row8' => [
                            'tableAtts' =>['cols' =>1,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                            'contents' => [
                                'titlerow' => [
                                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                    'contents' => ['row1' => [
                                        'tableAtts' => ['label' => $this->view->tr('quotetable')],
                                    ]],
                                ],
                               'addedRow1' => [
                                    'tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 50],
                                    'widgets' => ['daysvalid', 'referenceprefix', 'rowId', 'catalogid', 'comments'],
                                ],
                                'addedRow2' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                                    'widgets' => ['update'],
                                ],
                                'addedRow3' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                                    'widgets' => ['quotetable'],
                                ],
                            ],
                        ],
                    ],
                ],                          
                'onOpenAction' => 
                    "return this.serverAction(" .
                    	"{action: 'process', query: {id: true, params: " . json_encode(['process' => 'quoteTable', 'noget' => true]) . "}}, " .
                    	"{includeWidgets: ['daysvalid', 'rowId', 'catalogid', 'comments'], " .
						 "includeFormWidgets: ['id', 'name', 'parentid', 'reference', 'quotedate', 'items', 'discountwt', 'pricewot', 'pricewt', 'downpay']}).then(function(){"  .
                        	"return true;" .
                    "});" 
            ]
        ];
    }
}
?>
