<?php

namespace TukosLib\Objects\BusTrack\Invoices\Customers\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Objects\Views\LocalActions;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\Objects\BusTrack\ViewActionStrings as VAS;
use TukosLib\Objects\BusTrack\Invoices\Customers\Views\Edit\ViewActionStrings as EVAS;
use TukosLib\Objects\ViewUtils;

class View extends EditView{

	use LocalActions;
	
	function __construct($actionController){
       parent::__construct($actionController);
       $customersOrSuppliers = $this->view->model->customersOrSuppliers;
       $tr = $this->view->tr;
       $customContents = [
            'row1' => [
                'tableAtts' => ['cols' => 9, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 80],
                'widgets' => ['id', 'organization', 'contact', 'relatedquote', 'parentid', 'name', 'reference', 'invoicedate', 'status']
            ],
            'row2' => [
                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['60%', '40%'], 'widgetCellStyle' => ['verticalAlign' => 'top']],
                'contents' => [
                    'col1' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                        'contents' => [
                            'row2' => ['tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true], 'widgets' => [ 'discountpc', 'discountwt', 'todeduce', 'lefttopay', 'pricewot', 'pricewt']],
                            'row1' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 'widgets' => [ 'items']],
                            'row3' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 'widgets' => [ 'payments', 'paymentsitems']],
                        ]
                    ],
                    'col2' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                        'widgets' => ['comments', 'catalog'],
                    ],
                ]
            ]
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowbottom', 'rowacl'], $this->dataLayout['contents']));
        $this->actionWidgets['export']['atts']['needToSaveBefore'] = true;
        $this->actionWidgets['export']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'widgetsDescription' => [
                	'template' => ['atts' => ['value' => Utl::utf8('<br>${§invoicetable}<br>${$@comments}')]],
                   	'catalogid' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showcatalogid'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('catalogid')])),
                   	'comments' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showcomments'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('comments')])),
                   	'discount' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showdiscount'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('discount')])),
                		'update' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('prepare'), 'hidden' => true, 'onClickAction' => 
                        "this.pane.serverAction(" .
                        	"{action: 'Process', query: {id: true, params: " . json_encode(['process' => 'invoiceTable', 'noget' => true]) . "}}, " .
                        	"{includeWidgets: ['catalogid', 'comments'], " .
                    		  "includeFormWidgets: ['id', 'name', 'parentid', 'reference', 'invoicedate', 'items', 'discountwt', 'pricewot', 'pricewt', 'todeduce']}).then(lang.hitch(this, function(){\n" .
                            "this.pane.previewContent();" .
                            "this.set('hidden', true);" .
                        "}));"  
                    ]],
                    'invoicetable'  => Widgets::editor(Widgets::complete(['title' => $this->view->tr('invoicetable'), 'hidden' => true])),
                    'paymenttable'  => Widgets::htmlContent(Widgets::complete(['title' => $this->view->tr('paymenttable'), 'hidden' => true])),
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
                                    'widgets' => ['catalogid', 'comments', 'discount'],
                                ],
                                'addedRow2' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                                    'widgets' => ['update'],
                                ],
                                'addedRow3' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                                    'widgets' => ['invoicetable', 'paymenttable'],
                                ],
                            ],
                        ],
                    ],
                ],                          
                'onOpenAction' => EVAS::onExportOpen($tr),
            ]
        ];
        $this->actionWidgets['quickentry'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('Quickentry'), 'allowSave' => true]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'quickentry';
        $this->actionWidgets['quickentry']['atts']['dialogDescription'] = [
            //'closeOnBlur' => true,
            'paneDescription' => [
                'widgetsDescription' => [
                    'organization' => Widgets::ObjectSelect(Widgets::complete(['title' => $tr('Organization'), 'object' => 'bustrackorganizations', 'hidden' => true])),
                    'customer' => Widgets::objectSelect(Widgets::complete(['title' => $tr('Customer'), 'object' => 'bustrackpeople'])),
                    'date' => Widgets::tukosDateBox(Widgets::complete(['title' => $tr('Date'), 'value' => date('Y-m-d')])),
                    'name' => Widgets::textBox(Widgets::complete(['title' => $tr('Description'), 'style' => ['width' => '15em']])),
                    'catalogid'    => Widgets::description(ViewUtils::objectSelect($this->view, 'CatalogId', 'bustrackcatalog', ['atts' => ['edit' => ['storeArgs' => ['storeDgrid' => 'catalog'], 'onChangeLocalAction' => [
                        'catalogid' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => false], 'action' => VAS::catalogIdLocalAction()]]]]]])),
                    'category' => Widgets::ObjectSelect(Widgets::complete(['title' => $tr('Category'), 'object' => 'bustrackcategories', 'style' => ['width' => '15em'], 'storeArgs' => ['cols' => ['vatfree']],
                        'dropdownFilters' => [["col" => "applyto{$customersOrSuppliers}", 'opr' => 'IN' , 'values' => ["YES", 1]]],
                        'onWatchLocalAction' => ['value' => ['vatfree' => ['checked' => ['triggers' => ['user' => true, 'server' => false], 'action' => "return sWidget.getItemProperty('vatfree') ? true : false;"]]]]])),
                    'vatfree' => Widgets::checkBox(Widgets::complete(['title' => $tr('vatfree'), 'onWatchLocalAction' => [
                        'checked' => ['vatfree' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => false], 'action' => VAS::vatfreeLocalAction()]]]]])),
                    'vatrate' => Widgets::tukosNumberBox(Widgets::complete(['title' => $tr('vatrate') . ' %', 'value' => 0.085, 'style' => ['width' => '4em'],
                        'constraints' => ['type' => 'percent', 'pattern' => '#.####%'], 'editOptions' => ['pattern' => '#.####%'],
                        'onWatchLocalAction' => ['value' => ['vatrate' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => true], 'action' => VAS::vatRateLocalAction()]]]]])),
                    'unitpricewot'  => Widgets::tukosCurrencyBox(Widgets::complete(['title' => $tr('unitpricewot'), 'style' => ['width' => '4em'], 'onChangeLocalAction' => ['unitpricewot' => ['localActionStatus' =>
                        "var quantity = sWidget.valueOf('#quantity'), vatFactor = 1+Number(sWidget.valueOf('#vatrate')), newUnitPriceWt = newValue * vatFactor;\n" .
                        "sWidget.setValueOf('#unitpricewt', newUnitPriceWt);\n" .
                        "sWidget.setValueOf('#pricewot', quantity *  newValue);\n" .
                        "sWidget.setValueOf('#pricewt', quantity *  newUnitPriceWt);\n" .
                        "return true;\n"
                    ]]])),
                    'comments' => Widgets::htmlContent(Widgets::complete(['title' => $tr('comments'), 'hidden' => true])),
                    'unitpricewt'  => Widgets::tukosCurrencyBox(Widgets::complete(['title' => $tr('Unitpricewt'), 'style' => ['width' => '4em'], 'onChangeLocalAction' => ['unitpricewot' => ['localActionStatus' =>
                        "var quantity = sWidget.valueOf('#quantity'), vatFactor = 1+Number(sWidget.valueOf('#vatrate')), newUnitPriceWot = newValue / vatFactor;\n" .
                        "sWidget.setValueOf('#unitpricewot', newUnitPriceWot);\n" .
                        "sWidget.setValueOf('#pricewot', quantity *  newUnitPriceWot);\n" .
                        "sWidget.setValueOf('#pricewt', quantity *  newValue);\n" .
                        "return true;\n"
                    ]]])),
                    'quantity'  => Widgets::textBox(Widgets::complete(['title' => $tr('quantity'), 'value' => 1, 'style' => ['width' => '4em'], 'onChangeLocalAction' => ['quantity' => ['localActionStatus' =>
                        "var priceWot = newValue * sWidget.valueOf('#unitpricewot'), priceWt = newValue * sWidget.valueOf('#unitpricewt');\n" .
                        "sWidget.setValueOf('#pricewot', priceWot);\n" .
                        "sWidget.setValueOf('#pricewt', priceWt);\n" .
                        "return true;\n"
                    ]]])),
                    'pricewot'  => Widgets::tukosCurrencyBox(Widgets::complete(['title' => $tr('pricewot'), 'style' => ['width' => '4em'], 'onChangeLocalAction' => ['pricewot' => ['localActionStatus' =>
                        "var quantity = sWidget.valueOf('#quantity'), unitPriceWot = sWidget.valueOf('#unitpricewot'), vatFactor = 1 + sWidget.valueOf('#vatrate');\n" .
                        "sWidget.setValueOf('pricewt', newValue * vatFactor);\n" .
                        "if (quantity){sWidget.setValueOf('#unitpricewt', newValue * vatFactor / quantity);sWidget.setValueOf('#unitpricewot', newValue / quantity);}\n" .
                        "return true;\n"
                    ]]])),
                    'pricewt'  => Widgets::tukosCurrencyBox(Widgets::complete(['title' => $tr('pricewt'), 'style' => ['width' => '4em'], 'onChangeLocalAction' => ['pricewt' => ['localActionStatus' =>
                        "var quantity = sWidget.valueOf('#quantity'), unitPriceWt = sWidget.valueOf('#unitpricewt'), vatFactor = 1 + Number(sWidget.valueOf('#vatrate'));\n" .
                        "sWidget.setValueOf('pricewot', newValue / vatFactor);\n" .
                        "if (quantity){sWidget.setValueOf('#unitpricewt', newValue / quantity);sWidget.setValueOf('#unitpricewot', newValue / quantity / vatFactor);}\n" .
                        "return true;\n"
                    ]]])),
                    'paymenttype' => Widgets::description(ViewUtils::StoreSelect('paymentType', $this->view, 'PaymentType', null, ['atts' => ['edit' => ['onChangeLocalAction' => [
                        'reference' => ['hidden' => "return newValue !== 'paymenttype1';"],
                        'slip' => ['hidden' => "return newValue !== 'paymenttype1';"]
                    ]]]])),
                    'reference' =>  Widgets::TextBox(Widgets::complete(['title' => $tr('paymentreference'), 'hidden' => true])),
                    'slip' =>  Widgets::TextBox(Widgets::complete(['title' => $tr('CheckSlipnumber'), 'hidden' => true])),
                    'close' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('close'), 'onClickAction' => "this.pane.close();\n"]],
                    'synchronize' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('Synchronize'), 'onClickAction' => VAS::synchronizeOnClickAction()]],
                ],
                'layout' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                    'contents' => [
                        'row1' => [
                            'tableAtts' =>['cols' => 4,  'customClass' => 'labelsAndValues', 'showLabels' => true],
                            'widgets' => ['customer', 'catalogid', 'name', 'date'],
                        ],
                        'row2' => [
                            'tableAtts' =>['cols' => 8,  'customClass' => 'labelsAndValues', 'showLabels' => true],
                            'widgets' => ['category', 'vatfree', 'vatrate', 'unitpricewot', 'comments', 'unitpricewt', 'quantity', 'pricewot', 'pricewt'],
                        ],
                        'row3' => [
                            'tableAtts' =>['cols' => 8,  'customClass' => 'labelsAndValues', 'showLabels' => false],
                            'widgets' => ['paymenttype', 'reference', 'slip', 'organization'],
                        ],
                        'row4' => [
                            'tableAtts' =>['cols' => 2,  'customClass' => 'labelsAndValues', 'showLabels' => false],
                            'widgets' => ['close', 'synchronize'],
                        ],
                    ],
                ],
                 'onOpenAction' => <<<EOT
var invoiceParentId = this.form.valueOf('parentid'), organizationId = this.form.valueOf('organization');
if (invoiceParentId){
    this.setValueOf('customer', invoiceParentId);
}
if (organizationId){
    this.setValueOf('organization', organizationId);
}
return true;
EOT
            ]];
        $this->actionWidgets['quickpayment'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('Quickpayment'), 'allowSave' => true]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'quickpayment';
        $this->actionWidgets['quickpayment']['atts']['dialogDescription'] = [
            //'closeOnBlur' => true,
            'paneDescription' => [
                'widgetsDescription' => [
                    'customer' => Widgets::description(ViewUtils::objectSelectMulti(['bustrackpeople', 'bustrackorganizations'], $this->view, 'Customer')),
                    'organization' => Widgets::objectSelect(Widgets::complete(['title' => $tr('Payingorganization'),'object' => 'bustrackorganizations'])),
                    'date' => Widgets::tukosDateBox(Widgets::complete(['title' => $tr('Date'), 'value' => date('Y-m-d')])),
                    'name' => Widgets::textBox(Widgets::complete(['title' => $tr('Description'), 'style' => ['width' => '15em']])),
                    'pricewt'  => Widgets::tukosCurrencyBox(Widgets::complete(['title' => $tr('pricewt'), 'style' => ['width' => '4em']])),
                    'invoiceitemid' => Widgets::objectSelect(Widgets::complete(['title' => $tr('Invoiceitem'),'object' => 'bustrackinvoicesitems', 'storeArgs' => ['storeDgrid' => 'items']])),
                    'category' => Widgets::ObjectSelect(Widgets::complete(['title' => $tr('Category'), 'object' => 'bustrackcategories', 'style' => ['width' => '15em'], 'storeArgs' => ['cols' => ['vatfree']],
                        'dropdownFilters' => [["col" => "applyto{$customersOrSuppliers}", 'opr' => 'IN' , 'values' => ["YES", 1]]],
                        'onWatchLocalAction' => ['value' => ['vatfree' => ['checked' => ['triggers' => ['user' => true, 'server' => false], 'action' => "return sWidget.getItemProperty('vatfree') ? true : false;"]]]]])),
                    'paymenttype' => Widgets::description(ViewUtils::StoreSelect('paymentType', $this->view, 'PaymentType', null, ['atts' => ['edit' => ['onChangeLocalAction' => [
                        'reference' => ['hidden' => "return newValue !== 'paymenttype1';"],
                        'slip' => ['hidden' => "return newValue !== 'paymenttype1';"]
                    ]]]])),
                    'reference' =>  Widgets::TextBox(Widgets::complete(['title' => $tr('paymentreference'), 'hidden' => true])),
                    'slip' =>  Widgets::TextBox(Widgets::complete(['title' => $tr('CheckSlipnumber'), 'hidden' => true])),
                    'close' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('close'), 'onClickAction' => "this.pane.close();\n"]],
                    'synchronize' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('Synchronize'), 'onClickAction' => VAS::synchronizePaymentOnClickAction()]],
                ],
                'layout' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                    'contents' => [
                        'row1' => [
                            'tableAtts' =>['cols' => 4,  'customClass' => 'labelsAndValues', 'showLabels' => true],
                            'widgets' => ['customer', 'name', 'pricewt', 'date'],
                        ],
                        'row3' => [
                            'tableAtts' =>['cols' => 8,  'customClass' => 'labelsAndValues', 'showLabels' => false],
                            'widgets' => ['invoiceitemid', 'paymenttype', 'reference', 'slip'],
                        ],
                        'row4' => [
                            'tableAtts' =>['cols' => 2,  'customClass' => 'labelsAndValues', 'showLabels' => false],
                            'widgets' => ['close', 'synchronize'],
                        ],
                    ],
                ],
                'onOpenAction' => <<<EOT
this.setValueOf('customer', this.form.valueOf('parentid'));    
return true;
EOT
            ]];
	}
}
?>
