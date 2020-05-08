<?php
namespace TukosLib\Objects\BusTrack\Reconciliations;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Widgets;
use TukosLib\Objects\BusTrack\ViewActionStrings as VAS;

class View extends AbstractView {
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        $tr = $this->tr;
        $customDataWidgets = [
            'comments' => ['atts' => ['edit' => ['height' => '80px']]],
            'startdate' => ViewUtils::tukosDateBox($this, 'Periodstart'),
            'enddate' => ViewUtils::tukosDateBox($this, 'Periodend'),
            'paymentslog' => ViewUtils::jsonGrid($this, 'Reconciliationstate', [
                    'selector' => ['selector' => 'checkbox', 'width' => 30],
                //'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'id' => ['field' => 'id', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'renderExpando' => true],
                'parentid' => ['field' => 'parentid', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'paymentid' => Widgets::description(ViewUtils::ObjectSelect($this, 'Paymentid', 'bustrackpayments'), false),
                    //'id' => ['label' => $tr('Paymentid'), 'field' => 'id', 'width' => 80],
                //'date' => ['label' => $tr('date'), 'field' => 'date', 'width' => 100],
                'date' => Widgets::description(ViewUtils::tukosDateBox($this, 'date'), false),
                //'description' => ['label' => $tr('description'), 'field' => 'description', 'width' => 600],
                    'description' =>  Widgets::description(ViewUtils::textArea($this, 'description', ['atts' => [
                        'edit' => ['style' => ['width' => '600px']], 'storeedit' => ['width' => 600], 'overview' => ['width' => 600]]]), false),
                    //'amount' => ['label' => $tr('amount'), 'field' => 'amount', 'renderCell' => 'renderContent', 'formatType' => 'currency', 'width' => 80],
                    'amount' => Widgets::description(ViewUtils::tukosCurrencyBox($this, 'Amount', ['atts' => ['storeedit' => ['formatType' => 'currency', 'width' => 80]]]), false),
                'isexplained' => Widgets::description(ViewUtils::checkBox($this, 'Isexplained', ['atts' => ['storeedit' => ['width' => 80]]]), false),
                    'customer' => Widgets::description(ViewUtils::objectSelectMulti(['bustrackpeople', 'bustrackorganizations'], $this, 'Customer', ['atts' => ['edit' => ['style' => ['width' => '100px']]]]), false),
                    'category' => Widgets::description(ViewUtils::ObjectSelect($this, 'Category', 'bustrackcategories'), false),
                    'paymenttype' => Widgets::description(ViewUtils::storeSelect('paymentType', $this, 'paymenttype'), false),
                    'reference' =>  Widgets::description(ViewUtils::textBox($this, 'Paymentreference'), false),
                    'slip' =>  Widgets::description(ViewUtils::textBox($this, 'CheckSlipNumber'), false),
                    'createinvoice' => Widgets::description(ViewUtils::checkBox($this, 'Createinvoice', ['atts' => ['storeedit' => ['width' => 80]]]), false),
                    'invoiceid'     => Widgets::description(ViewUtils::objectSelect($this, 'Invoice', 'bustrackinvoices', ['atts' => [
                        'edit' => ['dropdownFilters' => ['organization' => '@parentid', ['col' => 'lefttopay', 'opr' => '>', 'values' => 0]]],
                        'storeedit' => ['width' => 100]
                    ]]), false),
                    'invoiceitemid' => Widgets::description(ViewUtils::objectSelect($this, 'Invoiceitem', 'bustrackinvoicesitems', ['atts' => [
                        'edit' => ['dropdownFilters' => ['parentid' => '#invoiceid']],
                        'storeedit' => ['width' => 100]]]), false),
                ],
                ['type' => 'StoreDgrid', 'atts' => ['edit' => ['object' => 'bustrackpayments', 'objectIdCols' => ['paymentid', 'customer', 'category', 'invoiceid', 'invoiceitemid'], 'allowSelectAll' => true, 'maxHeight' => '400px',
                    'minRowsPerPage' => 500, 'maxRowsPerPAge' => 500,]]]
            ),
        ];
        $this->customize($customDataWidgets, [], ['grid' => ['paymentslog']], ['paymentslog' => []]);
    }
}
?>
