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
            'unexplainedpaymentsonly' => ViewUtils::checkBox($this, 'Unexplainedpaymentsonly'),
            'verificationcorrections' => ViewUtils::checkBox($this, 'Verificationcorrections'),
            'pendinginvoicesonly' => ViewUtils::checkBox($this, 'Showpendinginvoicesonly'),
            'showinvoicessince' => ViewUtils::tukosDateBox($this, 'Showinvoicessince'),
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
                'isexplained' => Widgets::description(ViewUtils::checkBox($this, 'Isexplained', ['atts' => ['storeedit' => ['width' => 80, 'editorArgs' => [
                        'onChangeLocalAction' => ['isexplained' => ['localActionStatus' => $this->updatedRow()]]]]]]), false),
                'customer' => Widgets::description(ViewUtils::objectSelectMulti(['bustrackpeople', 'bustrackorganizations'], $this, $paidByOrTo, ['atts' => ['edit' => [/*'allowManualInput' => true, */'style' => ['width' => '100px']]]]), false),
                'category' => Widgets::description(ViewUtils::ObjectSelect($this, 'Category', 'bustrackcategories', ['atts' => ['edit' => [
                    'dropdownFilters' => ['parentid' => '@parentid', ["col" => "applyto{$customersOrSuppliers}", 'opr' => 'IN' , 'values' => ["YES", 1]]]]]]), false),
                'paymenttype' => Widgets::description(ViewUtils::storeSelect('paymentType', $this, 'paymenttype'), false),
                'reference' =>  Widgets::description(ViewUtils::textBox($this, 'Paymentreference'), false),
                'slip' =>  Widgets::description(ViewUtils::textBox($this, 'CheckSlipNumber'), false),
                'createinvoice' => Widgets::description(ViewUtils::checkBox($this, 'Createinvoice', ['atts' => ['storeedit' => ['width' => 80]]]), false),
                'invoiceid'     => Widgets::description(ViewUtils::objectSelect($this, 'Invoice', "bustrackinvoices{$customersOrSuppliers}", ['atts' => [
                        'edit' => [
                            'dropdownFilters' => $this->invoiceIdDropdownFilters(),
                            'storeArgs' => ['cols' => ['parentid']],
                            'onChangeLocalAction' => ['invoiceitemid' => ['value' => "return '';"], 'customer' => ['localActionStatus' => $this->invoiceIdCustomerLocalAction()]],
                        ],
                        'storeedit' => ['width' => 100]
                    ]]), false),
                'invoiceitemid' => Widgets::description(ViewUtils::objectSelect($this, 'Invoiceitem', "bustrackinvoices{$customersOrSuppliers}items", ['atts' => [
                        'edit' => [
                            'dropdownFilters' => ['parentid' => '#invoiceid'],
                            'storeArgs' => ['cols' => ['name', 'category', 'pricewt']],
                            'onChangeLocalAction' => ['invoiceitemid' => ['localActionStatus' => $this->invoiceItemIdLocalAction()]]
                        ],
                        'storeedit' => ['width' => 100]]]), false),
                ],
                ['type' => 'StoreDgrid', 'atts' => [
                    'edit' => [
                        'object' => 'bustrackpayments', 'objectIdCols' => ['paymentid', 'customer', 'category', 'invoiceid', 'invoiceitemid'], 'allowSelectAll' => true, 'maxHeight' => '500px', 'minRowsPerPage' => 500, 'maxRowsPerPage' => 500,
                        //'allowedNestedRowWatchActions' => 0,
                        'sort'            => [['property' => 'id', 'descending' => false]], 'deselectOnRefresh' => false,
                        'summaryRow' => ['cols' => [
                            'name' => ['content' =>  ['Total']],
                            'amount' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return res + Number(#amount#);"]]],
                        ]],
                        'createRowAction' => $this->createRowAction(),
                        'updateRowAction' => $this->updateRowAction(),
                        'deleteRowAction' => $this->deleteRowAction(),
                        //'afterExpandAction' => $this->afterExpandAction(),
                        'afterActions' => [
                            'expand' => $this->AfterExpand(),
                        ]
                ]]]
            ),
        ];
        $this->customize($customDataWidgets, [], ['grid' => ['paymentslog']], ['paymentslog' => []]);
    }
    public function updatedRow(){
        return <<<EOT
var grid = tWidget.column.grid, idp =grid.collection.idProperty, id = tWidget.row.data[idp];
    grid.updateDirty(id, 'updated', dutils.formatDate(new Date(), 'yyyy-MM-dd HH:mm:ss'));
return true;
EOT
        ;
    }
    public function paymentIdLocalAction(){
        return <<<EOT
var payment = sWidget.getItem(), mapping = {date: 'date', sname: 'description', amount: 'amount', isexplained: 'isexplained', parentid: 'customer', category: 'category', paymenttype: 'paymenttype', reference: 'reference', slip: 'slip'};
utils.forEach(mapping, function(target, source){
    if (payment[source]){    
        sWidget.setValueOf(target, payment[source]);
    }
});
return true;
EOT;
    }
    public function paymentIdDropdownFilters(){
        return <<<EOT
var filters = {0: {col: 'date', opr: '>=', values: dutils.dateString(widget.valueOf('@startdate'), [-30, 'day'])}, 1: {col: 'date', opr: '<=', values: widget.valueOf('@enddate')}}, 
    amount = Number.parseFloat(widget.valueOf('amount')).toFixed(2), i = 0, unexplainedOnly = widget.valueOf('@unexplainedpaymentsonly');
if (amount && !isNaN(amount)){
    filters.amount = amount;
}
['customer', 'category', 'paymenttype', 'reference'].forEach(function(col){
    var value = widget.valueOf(col), targetCol = col === 'customer' ? 'parentid' : col;
    if (value){
        i += 1;
        filters[i] = {0: {col: targetCol, opr: 'IS NULL', values : ''}, 1: {col: targetCol, opr: '=', values: value, or: true}};
    }
});
//console.log('isExplained: ' + isExplained);
if (unexplainedOnly){
    i += 1;
    filters[i] = {0: {col: 'isexplained', opr: 'IS NULL', values : ''}, 1: {col: 'isexplained', opr: '=', values: '', or: true}};
}
return filters;
EOT;
    }
    public function invoiceIdDropdownFilters(){
        return <<<EOT
var pendingInvoicesOnly = widget.valueOf('@pendinginvoicesonly'), showInvoicesSince = widget.valueOf('@showinvoicessince'), filters = {
    0: {col: 'organization', 'opr': 'LIKE', values: widget.valueOf('@parentid')}/*, 1: {col: 'invoicedate', opr: '<=', values: widget.valueOf('@enddate')}*/}, customer = widget.valueOf('customer');
if (pendingInvoicesOnly){
    filters[2] =  {col: 'lefttopay', opr: '>', values: 0};
}
if (showInvoicesSince){
    filters[3] = {col: 'invoicedate', opr: '>=', values: showInvoicesSince};
}
if (customer){
    filters.parentid = customer;
}
return filters;
EOT;
    }
    public function invoiceIdCustomerLocalAction(){
        return <<<EOT
if (newValue){
    var grid = tWidget.column.grid, idp = grid.collection.idProperty, id = tWidget.row.date[idp];
    grid.updateDirty(id, 'customer', sWidget.getItemProperty('parentid'));
}
EOT
        ;
    }
    public function invoiceItemIdLocalAction(){
        return <<<EOT
if (newValue){
    var grid = tWidget.column.grid, idp =grid.collection.idProperty, id = tWidget.row.data[idp];
    grid.updateDirty(id, 'category', sWidget.getItemProperty('category'));
    grid.updateDirty(id, 'description', sWidget.valueOf('description') || sWidget.getItemProperty('name'));
    grid.updateDirty(id, 'amount', sWidget.valueOf('amount') || sWidget.getItemProperty('pricewt'), false, true);
}
EOT
        ;
    }
    public function amountLocalAction(){
        return <<<EOT

console.log('in amountLocalAction');
var  parentid = tWidget.row.data.parentid;
if (parentid){
    var grid = tWidget.column.grid, collection = grid.collection, idProperty = collection.idProperty, delta = newValue - (oldValue || 0);
    var parentRow = collection.getSync(parentid);
    grid.updateDirty(parentid, 'amount', Number(parentRow.amount)-delta, false, true);
}
EOT;
    }
    public function createRowAction(){
        return <<<EOT
var parentid = row.parentid;
if (parentid){
    var parentRow = this.collection.getSync(parentid);
    row = lang.mixin(row, {date: parentRow.date, paymenttype: parentRow.paymenttype, slip: parentRow.slip});
}
EOT;
    }
    public function deleteRowAction(){
        return <<<EOT
var parentid = row.parentid, amount = row.amount;
if (parentid && amount){
    var collection = this.collection, parentRow = collection.getSync(parentid);
    this.updateDirty(parentid, 'amount', Number(parentRow.amount) + Number(amount));
}
EOT;
    }
    public function updateRowAction(){
        return <<<EOT
var parentid = row.parentid;
if (parentid){
    var collection = this.collection, id = row[this.collection.idProperty], existingRow = collection.getSync(id), newRow = lang.mixin(lang.clone(existingRow), row);
    if (newRow.amount !== existingRow.amount){
        var parentRow = collection.getSync(parentid);
        this.updateDirty(parentid, 'amount', Number(parentRow.amount) - newRow.amount + (existingRow.amount || 0));
    }
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
/*
    public function afterExpandAction(){
        return <<<EOT
    var amountCell = this.cell(args[1][0].element || args[1][0], 'amount'), value = this.cellValueOf('amount'); // column, element and row
    lang.hitch(amountCell.column, this.renderContent)(amountCell.row.data, value, amountCell.element, null, true);
EOT;
    }
*/
    public function afterExpand(){
        return <<<EOT
var self = this, row = arguments[1][0], element = row.element;
return arguments[0].then(function(){
    if (element){
        var amountCell = self.cell(element, 'amount'), value = self.cellValueOf('amount'); // column, element and row
        lang.hitch(amountCell.column, self.renderContent)(amountCell.row.data, value, amountCell.element, null, true);
    }
    return true;
});
EOT;
    }
}
?>
