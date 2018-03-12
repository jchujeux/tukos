<?php

namespace TukosLib\Objects\BusTrack\Invoices\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Objects\Views\LocalActions;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;

class View extends EditView{

	use LocalActions;
	
	function __construct($actionController){
       parent::__construct($actionController);

        $this->dataLayout   = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false,  'content' => ''],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 9, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 80],
                    'widgets' => ['id', 'relatedquote', 'parentid', 'name', 'reference', 'invoicedate', 'status']
                ],
                'row2' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['60%', '40%']],      
                    'contents' => [              
                        'col1' => [
                        	'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                       		'contents' => [
                       			'row1' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 'widgets' => [ 'items']],
                       			'row2' => ['tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true], 'widgets' => [ 'discountpc', 'discountwt', 'todeduce', 'pricewot', 'pricewt']],
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

        $this->actionWidgets['export']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'widgetsDescription' => [
                	'template' => ['atts' => ['value' => Utl::utf8('<br>${§invoicetable}<br>${$@comments}')]],
                	'referenceprefix' => Widgets::textBox(['label' => $this->view->tr('referenceprefix'), 'style' => ['width' => '6em'], 'onWatchLocalAction' => $this->watchLocalAction('referenceprefix')]),
                	'daysduedate' => Widgets::tukosNumberBox(['label' => $this->view->tr('daysduedate'), 'style' => ['width' => '4em'], 'onWatchLocalAction' => $this->watchLocalAction('daysduedate')]),
                		'rowId' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showrowId'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('rowId')])),
                   	'catalogid' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showcatalogid'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('catalogid')])),
                   	'comments' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showcomments'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('comments')])),
                   	'discount' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showdiscount'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('discount')])),
                		'update' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('prepare'), 'hidden' => true, 'onClickAction' => 
                        "this.pane.serverAction(" .
                        	"{action: 'process', query: {id: true, params: " . json_encode(['process' => 'invoiceTable', 'noget' => true]) . "}}, " .
                        	"{includeWidgets: ['daysduedate', 'rowId', 'catalogid', 'comments'], " .
                    		  "includeFormWidgets: ['id', 'name', 'parentid', 'reference', 'invoicedate', 'items', 'discountwt', 'pricewot', 'pricewt', 'todeduce']}).then(lang.hitch(this, function(){\n" .
                            "this.pane.previewContent();" .
                            "this.set('hidden', true);" .
                        "}));"  
                    ]],
                    'invoicetable'  => Widgets::editor(Widgets::complete(['title' => $this->view->tr('invoicetable'), 'hidden' => true])),
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
                                        'tableAtts' => ['label' => $this->view->tr('invoicetable')],
                                    ]],
                                ],
                               'addedRow1' => [
                                    'tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 50],
                                    'widgets' => ['daysduedate', 'referenceprefix', 'rowId', 'catalogid', 'comments', 'discount'],
                                ],
                                'addedRow2' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                                    'widgets' => ['update'],
                                ],
                                'addedRow3' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                                    'widgets' => ['invoicetable'],
                                ],
                            ],
                        ],
                    ],
                ],                          
                'onOpenAction' => 
                    "return this.serverAction(" .
                    	"{action: 'process', query: {id: true, params: " . json_encode(['process' => 'invoiceTable', 'noget' => true]) . "}}, " .
                    	"{includeWidgets: ['daysduedate', 'rowId', 'catalogid', 'comments'], " .
						 "includeFormWidgets: ['id', 'name', 'parentid', 'reference', 'invoicedate', 'items', 'discountwt', 'pricewot', 'pricewt', 'todeduce']}).then(function(){"  .
                        	"return true;" .
                    "});" 
            ]
        ];
    }
}
?>
