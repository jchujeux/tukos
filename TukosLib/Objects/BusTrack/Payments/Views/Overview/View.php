<?php

namespace TukosLib\Objects\BusTrack\Payments\Views\Overview;

use TukosLib\Objects\Views\Overview\View as OverviewView;
use TukosLib\Objects\BusTrack\Payments\Views\Overview\ViewActionStrings as VAS;
use TukosLib\Objects\Views\LocalActions;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Widgets;

class View extends OverviewView{
	
	use LocalActions;
	
	function __construct($actionController){
		parent::__construct($actionController);
		$tr = $this->view->tr;
		$this->actionWidgets['importpayments'] =  ['type' => 'OverviewAction', 'atts' => ['label' => $this->view->tr('Importpayments'), 'grid' => 'overview', 'serverAction' => 'Reset',
		    'queryParams' => ['process' => 'importPayments']]];
		$this->actionLayout['contents']['actions']['widgets'][] = 'importpayments';
       	$this->actionWidgets['importpayments']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'object' => 'bustrackpayments', 
                'viewMode' => 'overview',
                'widgetsDescription' => [
                    'startdate' => Widgets::tukosDateBox(['title' => $tr('synchrostart'), 'onWatchLocalAction' => $this->watchLocalAction('filename')]),
                    'enddate' => Widgets::tukosDateBox(['title' => $tr('synchroend'), 'onWatchLocalAction' => $this->watchLocalAction('filename')]),
                    'organization' => Widgets::description(ViewUtils::objectSelect($this->view, 'Receivingorganization', 'bustrackorganizations', ['atts' => ['edit' => ['onWatchLocalAction' => $this->watchLocalAction('organization')]]])),
                    'payments' => Widgets::storeDgrid(Widgets::complete(
                        ['label' => $tr('Importedpayments'), 'storeArgs' => ['idProperty' => 'idg'], 'initialId' => false, 'allowSelectAll' => true, 'maxHeight' => '530px', 'minRowsPerPage' => 500, 'maxRowsPerPAge' => 500,
                            'colsDescription' => [
                                'selector' => ['selector' => 'checkbox', 'width' => 30],
                                'id' => ['label' => $tr('Paymentid'), 'field' => 'id', 'width' => 80],
                                'date' => ['label' => $tr('date'), 'field' => 'date', 'width' => 100],
                                //'description' => ['label' => $tr('description'), 'field' => 'description', 'width' => 600],
                                'description' =>  Widgets::description(ViewUtils::textArea($this->view, 'description', ['atts' => [
                                    'edit' => ['style' => ['width' => '600px']], 'storeedit' => ['width' => 600], 'overview' => ['width' => 600]]]), false),
                                'amount' => ['label' => $tr('amount'), 'field' => 'amount', 'renderCell' => 'renderContent', 'formatType' => 'currency', 'width' => 80],
                                'customer' => Widgets::description(ViewUtils::objectSelectMulti(['bustrackpeople', 'bustrackorganizations'], $this->view, 'Customer', ['atts' => ['edit' => ['style' => ['width' => '100px']]]]), false),
                                'category' => Widgets::description(ViewUtils::ObjectSelect($this->view, 'Category', 'bustrackcategories'), false),
                                'paymenttype' => Widgets::description(ViewUtils::storeSelect('paymentType', $this->view, 'paymenttype'), false),
                                'reference' =>  Widgets::description(ViewUtils::textBox($this->view, 'Paymentreference'), false),
                                'slip' =>  Widgets::description(ViewUtils::textBox($this->view, 'CheckSlipNumber'), false),
                                'createinvoice' => Widgets::description(ViewUtils::checkBox($this->view, 'Createinvoice'), false),
                                'invoiceid'     => Widgets::description(ViewUtils::objectSelect($this->view, 'Invoice', 'bustrackinvoices', ['atts' => [
                                    'edit' => ['dropdownFilters' => ['organization' => '@organization', ['col' => 'lefttopay', 'opr' => '>', 'values' => 0]]],
                                    'storeedit' => ['width' => 100]
                                ]]), false),
                                'invoiceitemid' => Widgets::description(ViewUtils::objectSelect($this->view, 'Invoiceitem', 'bustrackinvoicesitems', ['atts' => [
                                    'edit' => ['dropdownFilters' => ['parentid' => '#invoiceid']],
                                    'storeedit' => ['width' => 100]]]), false),
                            ]])),
                    'import'  => ['type' => 'SimpleUploader', 'atts' => ['label' => $this->view->tr('Import'), 'multiple' => false, 'uploadOnSelect' => true, 'grid' => 'overview', 'serverAction' => 'Process',
                        'includeWidgets' => ['startdate', 'enddate', 'organization'], 'queryParams' => ['process' => 'importPayments'], 'onCompleteAction' => VAS::onImportCompleteAction()]],
                    'synchronize' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('synchronize'), 'includeWidgets' => ['payments'], 'onClickAction' => VAS::syncOnClickAction()]],
                    'close' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('close'), 'onClickAction' => "this.pane.close();\n"]],
                    'cancel' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('cancel'), 'onClickAction' => "this.pane.close();"]],
                ],
                'layout' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                    'contents' => [
                        'row1' => [
                            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                            'contents' => [
                                'col1' => [
                                    'tableAtts' =>['cols' => 3,  'customClass' => 'labelsAndValues', 'showLabels' => true],
                                    'widgets' => ['startdate', 'enddate', 'organization'],
                                ],
                                'col2' => [
                                    'tableAtts' =>['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => false],
                                    'widgets' => ['import'],
                                ]
                            ]
                        ],
                        'row2' => [
                            'tableAtts' => ['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                            'widgets' => ['payments']
                        ],
                        'row3' => [
                            'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                            'widgets' => ['close', 'cancel', 'synchronize'],
                        ]
                    ]
                ],
                'style' => ['width' => '1600px'],
            ]
       	];
	}
}
?>