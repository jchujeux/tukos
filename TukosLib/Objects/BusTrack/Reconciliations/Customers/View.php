<?php
namespace TukosLib\Objects\BusTrack\Reconciliations\Customers;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Widgets;

class View extends AbstractView {
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        $customersOrSuppliers = $this->model->customersOrSuppliers;
        $paidByOrTo = ['customers' => 'Customer', 'suppliers' => 'Supplier'][$customersOrSuppliers];       
        $customDataWidgets = [
            'comments' => ['atts' => ['edit' => ['height' => '80px']]],
            'startdate' => ViewUtils::tukosDateBox($this, 'Periodstart'),
            'enddate' => ViewUtils::tukosDateBox($this, 'Periodend'),
            'nocreatepayments' => ViewUtils::checkBox($this, 'Nocreatepaymentsonsync'),
            'verificationcorrections' => ViewUtils::checkBox($this, 'Verificationcorrections'),
            'paymentslog' => ViewUtils::jsonGrid($this, 'Reconciliationstate', [
                'selector' => ['selector' => 'checkbox', 'width' => 30],
                'id' => ['field' => 'id', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'renderExpando' => true],
                'parentid' => ['field' => 'parentid', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'paymentid' => Widgets::description(ViewUtils::ObjectSelect($this, 'Paymentid', "bustrackpayments{$customersOrSuppliers}", ['atts' => [
                    'edit' => ['style' => ['width' => '200px'], 'dropdownFilters' => $this->paymentIdDropdownFilters(),
                        'storeArgs' => ['cols' => ['date', /*'name', */'amount', 'isexplained', 'parentid', 'category', 'paymenttype', 'reference', 'slip']],
                        'onChangeLocalAction' => ['paymentid' => ['localActionStatus' => $this->paymentIdLocalAction()]],
                        /*'customizableAtts' => []*/],
                    'storeedit' => ['width' => 200]
                ]]), false),
                'date' => Widgets::description(ViewUtils::tukosDateBox($this, 'date'), false),
                'description' =>  Widgets::description(ViewUtils::textArea($this, 'description', ['atts' => [
                        'edit' => ['style' => ['width' => '500px']], 'storeedit' => ['width' => 500], 'overview' => ['width' => 500]]]), false),
                'amount' => Widgets::description(ViewUtils::tukosCurrencyBox($this, 'Amount', ['atts' => [
                    'edit' => ['onChangeLocalAction' => ['amount' => ['localActionStatus' => $this->amountLocalAction()]]],
                    'storeedit' => ['formatType' => 'currency', 'width' => 80, 'renderContentAction' => $this->amountRenderContentAction()]]]), false),
                'isexplained' => Widgets::description(ViewUtils::checkBox($this, 'Isexplained', ['atts' => ['storeedit' => ['width' => 80]]]), false),
                'customer' => Widgets::description(ViewUtils::objectSelectMulti(['bustrackpeople', 'bustrackorganizations'], $this, $paidByOrTo, ['atts' => ['edit' => ['allowManualInput' => true, 'style' => ['width' => '100px']]]]), false),
                'category' => Widgets::description(ViewUtils::ObjectSelect($this, 'Category', 'bustrackcategories', ['atts' => ['edit' => [
                    'dropdownFilters' => ['parentid' => '@parentid', ["col" => "applyto{$customersOrSuppliers}", 'opr' => 'IN' , 'values' => ["YES", 1]]]]]]), false),
                'paymenttype' => Widgets::description(ViewUtils::storeSelect('paymentType', $this, 'paymenttype'), false),
                'reference' =>  Widgets::description(ViewUtils::textBox($this, 'Paymentreference'), false),
                'slip' =>  Widgets::description(ViewUtils::textBox($this, 'CheckSlipNumber'), false),
                'createinvoice' => Widgets::description(ViewUtils::checkBox($this, 'Createinvoice', ['atts' => ['storeedit' => ['width' => 80]]]), false),
                'invoiceid'     => Widgets::description(ViewUtils::objectSelect($this, 'Invoice', "bustrackinvoices{$customersOrSuppliers}", ['atts' => [
                        'edit' => ['dropdownFilters' => ['organization' => '@parentid', ['col' => 'parentid', 'opr' => 'LIKE', 'values' => '#customer'], ['col' => 'invoicedate', 'opr' => '<=', 'values' => '@enddate']],
                            'onChangeLocalAction' => ['invoiceitemid' => ['value' => "return '';"]]],
                        'storeedit' => ['width' => 100]
                    ]]), false),
                'invoiceitemid' => Widgets::description(ViewUtils::objectSelect($this, 'Invoiceitem', "bustrackinvoices{$customersOrSuppliers}items", ['atts' => [
                        'edit' => ['dropdownFilters' => ['parentid' => '#invoiceid']],
                        'storeedit' => ['width' => 100]]]), false),
                ],
                ['type' => 'StoreDgrid', 'atts' => [
                    'edit' => [
                        'object' => 'bustrackpayments', 'objectIdCols' => ['paymentid', 'customer', 'category', 'invoiceid', 'invoiceitemid'], 'allowSelectAll' => true, 'maxHeight' => '500px', 'minRowsPerPage' => 500, 'maxRowsPerPAge' => 500,
                        'sort'            => [['property' => 'id', 'descending' => false]],
                        'summaryRow' => ['cols' => [
                            'name' => ['content' =>  ['Total']],
                            'amount' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return Number(#amount#);"]]],
                        ]],
                        'deleteRowAction' => $this->deleteRowAction(),
                        'afterExpandAction' => $this->afterExpandAction()
                    ]]]
            ),
        ];
        $this->customize($customDataWidgets, [], ['grid' => ['paymentslog']], ['paymentslog' => []]);
    }
    public function paymentIdLocalAction(){
        return <<<EOT
var payment = sWidget.getItem(), mapping = {date: 'date', sname: 'description', amount: 'amount', isexplained: 'isexplained', parentid: 'customer', category: 'category', paymenttype: 'paymenttype', reference: 'reference', slip: 'slip'};
utils.forEach(mapping, function(target, source){
    if (payment[source]){    
        sWidget.setValueOf(target, payment[source]);
    }
});
['createinvoice', 'invoiceid', 'invoiceitemid'].forEach(function(col){
    sWidget.setValueOf(col, '');
});
return true;
EOT;
    }
    public function paymentIdDropdownFilters(){
        return <<<EOT
var filters = {0: {col: 'date', opr: '>=', values: dutils.dateString(widget.valueOf('@startdate'), [-30, 'day'])}, 1: {col: 'date', opr: '<=', values: widget.valueOf('@enddate')}}, 
    amount = Number.parseFloat(widget.valueOf('amount')).toFixed(2), customer = widget.valueOf('customer'), isExplained = widget.valueOf('isexplained');
if (amount){
    filters.amount = amount;
}
if (customer){
    filters.parentid = customer;
}
console.log('isExplained: ' + isExplained);
if (!isExplained){
    filters[2] = {0: {col: 'isexplained', opr: 'IS NULL', values : ''}, 1: {col: 'isexplained', opr: '=', values: '', or: true}};
}
return filters;
EOT;
    }
    public function amountLocalAction(){
        return <<<EOT

console.log('in amountLocalAction');
var  parentid = tWidget.row.data.parentid;
if (parentid){
    var grid = tWidget.column.grid, collection = grid.collection, idProperty = collection.idProperty, delta = newValue - (oldValue || 0);
    var parentRow = collection.getSync(parentid);
    grid.updateDirty(parentid, 'amount', Number(parentRow.amount)-delta);
}
EOT;
    }
    public function deleteRowAction(){
        return <<<EOT
var parentid = row.parentid, amount = row.amount;
if (parentid && amount){
    var collection = this.collection, idProperty = collection.idProperty, parentRow = collection.getSync(parentid);
    this.updateDirty(parentid, 'amount', Number(parentRow.amount) + amount);
}
EOT;
    }
    public function amountRenderContentAction(){
        return <<<EOT
var column = this, grid = column.grid, store = column.grid.store;
if (!grid._expanded[args.object[store.idProperty]]){
    args.value = Number(args.value);
    store.getChildren(args.object).forEach(function(subRow){
    	args.value += Number(subRow.amount) || 0;
    });
    args.value = args.value.toString();
}
EOT;
    }
    public function afterExpandAction(){
        return <<<EOT
var amountCell = this.cell(args[1][0].element, 'amount'), value = this.cellValueOf('amount'); // column, element and row
lang.hitch(amountCell.column, this.renderContent)(amountCell.row.data, value, amountCell.element, null, true);
EOT;
    }
}
?>
