<?php
namespace TukosLib\Objects\BusTrack\Invoices\Views\Edit;

class ViewActionStrings{
    static function onExportOpen($tr){
        return <<<EOT
var self = this, form = this.form, html = '', tableAtts = 'style="text-align:center; border: solid black;border-collapse: collapse;margin-left:auto;margin-right: auto;width:70%;"', 
    thAtts = style="border: 1px solid;border-collapse: collapse;padding: 2px;" ,  tdAtts = 'style="border: 1px solid;border-collapse: collapse;padding: 2px;"',
    paymentsItemsWidget = form.getWidget('paymentsitems'), paymentsWidget = form.getWidget('payments'), filter = new paymentsWidget.collection.Filter(), payment, leftToPay,
    rows = [{tag: 'tr', content: [{tag: 'th', atts: 'colspan=6 style="border: solid black;"', content: "{$tr('Paymentsmade')}"}]}], rowContent = [], itemCols = ['name', 'amount'], paymentCols = ['date', 'paymenttype', 'reference'];
paymentCols.forEach(function(col){
    rowContent.push({tag: 'th', atts: tdAtts, content: paymentsWidget.colDisplayedTitle(col)});
});
itemCols.forEach(function(col){
    rowContent.push({tag: 'th', atts: tdAtts, content: paymentsItemsWidget.colDisplayedTitle(col)});
});
rows.push({tag: 'tr', content: rowContent});
paymentsItemsWidget.collection.forEach(function(paymentItem){
    rowContent = [];
    payment = paymentsWidget.store.filter(filter.eq('id', paymentItem.parentid)).fetchSync()[0];
    paymentCols.forEach(function(col){
        rowContent.push({tag: 'td', atts: tdAtts, content: paymentsWidget.colDisplayedValue(payment[col], col)});
    });
    itemCols.forEach(function(col){
        rowContent.push({tag: 'td', atts: tdAtts, content: paymentsItemsWidget.colDisplayedValue(paymentItem[col], col)});
    });
    rows.push({tag: 'tr', content: rowContent});
});
if (rows.length > 2){
    leftToPay = form.valueOf('lefttopay');
    rows.push({tag: 'tr', content: [{tag: 'td', atts: 'colspan="3"' }, {tag: 'td', atts: tdAtts, content: "<b>{$tr('Remainingbalance')}</b>"}, {tag: 'td', atts: tdAtts, content: '<b>' + utils.transform(leftToPay, 'currency') + '</b>'}]});
    html = hiutils.buildHtml(['<br>', {tag: 'table', atts: tableAtts, content: [rows]}, '<br>']);
}
this.getWidget('paymenttable').set('value', html);
return this.serverAction(
    {action: 'Process', query: {id: true, params: {process: 'invoiceTable', noget: true}}},
    {includeWidgets: ['catalogid', 'comments'], 
    includeFormWidgets: ['id', 'name', 'parentid', 'reference', 'invoicedate', 'items', 'discountwt', 'pricewot', 'pricewt', 'todeduce']}).then(lang.hitch(this, function(){
       return true;
    }));
EOT;
    }
}
?>