<?php
namespace TukosLib\Objects\BusTrack\Reconciliations;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Widgets;

class View extends AbstractView {
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        $customDataWidgets = [
            'comments' => ['atts' => ['edit' => ['height' => '80px']]],
            'startdate' => ViewUtils::tukosDateBox($this, 'Periodstart'),
            'enddate' => ViewUtils::tukosDateBox($this, 'Periodend'),
            'nocreatepayments' => ViewUtils::checkBox($this, 'Nocreatepaymentsonsync'),
            'paymentslog' => ViewUtils::jsonGrid($this, 'Reconciliationstate', [
                'selector' => ['selector' => 'checkbox', 'width' => 30],
                'id' => ['field' => 'id', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'renderExpando' => true],
                'parentid' => ['field' => 'parentid', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'paymentid' => Widgets::description(ViewUtils::ObjectSelect($this, 'Paymentid', 'bustrackpayments', ['atts' => [
                    //'edit' => ['style' => ['width' => '200px'], 'dropdownFilters' => [['col' => 'isexplained', 'opr' => '<>', 'values' => 'YES'], ['col' => 'isexplained', 'opr' => '<>', 'values' => 1]]],
                    //'edit' => ['style' => ['width' => '200px'], 'dropdownFilters' => [['col' => 'date', 'opr' => '>=', 'values' => '@startdate'], ['col' => 'date', 'opr' => '<=', 'values' => '@enddate']],
                    'edit' => ['style' => ['width' => '200px'], 'dropdownFilters' => $this->paymentIdDropdownFilters(),
                        'storeArgs' => ['cols' => ['date', /*'name', */'amount', 'isexplained', 'parentid', 'category', 'paymenttype', 'reference', 'slip']],
                        'onChangeLocalAction' => ['paymentid' => ['localActionStatus' => $this->paymentIdLocalAction()]],
                        /*'customizableAtts' => []*/],
                    'storeedit' => ['width' => 200]
                ]]), false),
                'date' => Widgets::description(ViewUtils::tukosDateBox($this, 'date'), false),
                'description' =>  Widgets::description(ViewUtils::textArea($this, 'description', ['atts' => [
                        'edit' => ['style' => ['width' => '500px']], 'storeedit' => ['width' => 500], 'overview' => ['width' => 500]]]), false),
                'amount' => Widgets::description(ViewUtils::tukosCurrencyBox($this, 'Amount', ['atts' => ['storeedit' => ['formatType' => 'currency', 'width' => 80]]]), false),
                'isexplained' => Widgets::description(ViewUtils::checkBox($this, 'Isexplained', ['atts' => ['storeedit' => ['width' => 80]]]), false),
                'customer' => Widgets::description(ViewUtils::objectSelectMulti(['bustrackpeople', 'bustrackorganizations'], $this, 'Customer', ['atts' => ['edit' => ['allowManualInput' => true, 'style' => ['width' => '100px']]]]), false),
                'category' => Widgets::description(ViewUtils::ObjectSelect($this, 'Category', 'bustrackcategories', ['atts' => ['edit' => ['dropdownFilters' => ['parentid' => '@parentid']]]]), false),
                'paymenttype' => Widgets::description(ViewUtils::storeSelect('paymentType', $this, 'paymenttype'), false),
                'reference' =>  Widgets::description(ViewUtils::textBox($this, 'Paymentreference'), false),
                'slip' =>  Widgets::description(ViewUtils::textBox($this, 'CheckSlipNumber'), false),
                'createinvoice' => Widgets::description(ViewUtils::checkBox($this, 'Createinvoice', ['atts' => ['storeedit' => ['width' => 80]]]), false),
                'invoiceid'     => Widgets::description(ViewUtils::objectSelect($this, 'Invoice', 'bustrackinvoices', ['atts' => [
                        'edit' => ['dropdownFilters' => ['organization' => '@parentid', ['col' => 'parentid', 'opr' => 'LIKE', 'values' => '#customer'], ['col' => 'invoicedate', 'opr' => '<=', 'values' => '@enddate']],
                            'onChangeLocalAction' => ['invoiceitemid' => ['value' => "return '';"]]],
                        'storeedit' => ['width' => 100]
                    ]]), false),
                'invoiceitemid' => Widgets::description(ViewUtils::objectSelect($this, 'Invoiceitem', 'bustrackinvoicesitems', ['atts' => [
                        'edit' => ['dropdownFilters' => ['parentid' => '#invoiceid']],
                        'storeedit' => ['width' => 100]]]), false),
                ],
                ['type' => 'StoreDgrid', 'atts' => ['edit' => ['object' => 'bustrackpayments', 'objectIdCols' => ['paymentid', 'customer', 'category', 'invoiceid', 'invoiceitemid'], 'allowSelectAll' => true, 'maxHeight' => '500px',
                    'minRowsPerPage' => 500, 'maxRowsPerPAge' => 500,]]]
            ),
        ];
        $this->customize($customDataWidgets, [], ['grid' => ['paymentslog']], ['paymentslog' => []]);
    }
    public function paymentIdLocalAction(){
        return <<<EOT
var payment = sWidget.getItem(), mapping = {date: 'date', sname: 'description', amount: 'amount', isexplained: 'isexplained', parentid: 'customer', category: 'category', paymenttype: 'paymenttype', reference: 'reference', slip: 'slip'};
utils.forEach(mapping, function(target, source){
    sWidget.setValueOf(target, payment[source] || '');
});
['createinvoice', 'invoiceid', 'invoiceitemid'].forEach(function(col){
    sWidget.setValueOf(col, '');
});
return true;
EOT;
    }
    public function paymentIdDropdownFilters(){
        return <<<EOT
var filters = {0: {col: 'date', opr: '>=', values: dutils.dateString(widget.valueOf('@startdate'), [-30, 'day'])}, 1: {col: 'date', opr: '<=', values: widget.valueOf('@enddate')}, 
               2:{0: {col: 'isexplained', opr: 'IS NULL', values : ''}, 1: {col: 'isexplained', opr: '=', values: '', or: true}}}, amount = widget.valueOf('amount'), customer = widget.valueOf('customer');
if (amount){
    filters.amount = amount;
}
if (customer){
    filters.parentid = customer;
}
return filters;
EOT;
    }
}
?>
