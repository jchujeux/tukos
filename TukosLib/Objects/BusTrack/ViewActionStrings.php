<?php
namespace TukosLib\Objects\BusTrack;


class ViewActionStrings{

    public static function priceStringNoVat($unitPriceOnly){
        return $unitPriceOnly ? '' : ", '#pricewt': sWidget.valueOf('#pricewot')";
    }
    public static function priceStringVat($unitPriceOnly, $vatString){
        return $unitPriceOnly ? '' : ", '#pricewt': sWidget.valueOf('#pricewot') * (1 + $vatString)";
    }
    public static function vatfreeLocalAction($unitPriceOnly= false){
        $priceStringNoVat =  self::priceStringNoVat($unitPriceOnly);
        $priceStringVat =  self::priceStringVat($unitPriceOnly, "0.085");
        return <<<EOT
if (newValue){
    sWidget.setValuesOf({'#vatfree': 'YES', '#vatrate': 0, '#unitpricewt': sWidget.valueOf('#unitpricewot'){$priceStringNoVat}});
}else{
    sWidget.setValuesOf({'#vatfree': '', '#vatrate': 0.085, '#unitpricewt': sWidget.valueOf('#unitpricewot')* (1+0.085){$priceStringVat}});
}
return true;
EOT;
    }
    public static function vatRateLocalAction($unitPriceOnly= false){
        $priceStringNoVat =  self::priceStringNoVat($unitPriceOnly);
        $priceStringVat =  self::priceStringVat($unitPriceOnly, "newValue");
        return <<<EOT
if (newValue){
    sWidget.setValuesOf({'#vatfree': '', '#unitpricewt': sWidget.valueOf('#unitpricewot')*(1+newValue){$priceStringVat}});
}else{
    sWidget.setValuesOf({'#vatfree': 'YES', '#unitpricewt': sWidget.valueOf('#unitpricewot'){$priceStringVat}});
}
return true;
EOT;
    }
    public static function synchronizeOnClickAction(){
        return <<<EOT
var fields = ['customer', 'name', 'date', 'category', 'vatfree', 'vatrate', 'unitpricewot', 'unitpricewt', 'quantity', 'pricewot', 'pricewt', 'paymenttype', 'reference', 'slip'],
    invoicesFields = {customer: 'parentid', date: 'invoicedate', name: 'name'},
    invoicesItemsFields = {name: 'name', category: 'category', vatfree: 'vatfree', vatrate: 'vatrate', unitpricewot: 'unitpricewot', unitpricewt: 'unitpricewt', quantity: 'quantity', pricewot: 'pricewot', pricewt: 'pricewt'},
    paymentsFields = {customer: 'parentid', name: 'name', date: 'date', paymenttype: 'paymenttype', pricewt: 'amount'},
    paymentsItemsFields = {name: 'name', pricewt: 'amount'}, pane = this.pane, form = pane.form,
    invoicesItemsGrid = form.getWidget('items'), paymentsItemsGrid = form.getWidget('paymentsitems'), paymentsGrid = form.getWidget('payments'), fieldsValues = {}, invoicesItemsRow={}, paymentsItemsRow = {}, paymentsRow = {};
fields.forEach(function(field){
    fieldsValues[field] = pane.valueOf(field);
});
utils.forEach(invoicesFields, function(invoicesField, field){
    form.setValueOf(invoicesField, fieldsValues[field]);
});
utils.forEach(invoicesItemsFields, function(invoicesItemsField, field){
    invoicesItemsRow[invoicesItemsField] =  fieldsValues[field];
});
invoicesItemsGrid.addRow(undefined, invoicesItemsRow);
invoicesItemsGrid.setSummary();
if (fieldsValues.paymenttype){
    utils.forEach(paymentsFields, function(paymentsField, field){
        paymentsRow[paymentsField] =  fieldsValues[field];
    });
    if (fieldsValues.paymenttype === 'paymenttype1'){
        ['reference', 'slip'].forEach(function(field){
            paymentsRow[field] = fieldsValues[field];
        });
    }
    paymentsGrid.addRow(undefined, paymentsRow);
    utils.forEach(paymentsItemsFields, function(paymentsItemsField, field){
        paymentsItemsRow[paymentsItemsField] =  fieldsValues[field];
    });
    paymentsItemsRow.invoiceitemid = invoicesItemsGrid.newRowPrefix + invoicesItemsGrid.getLastNewRowPrefixId();
    paymentsItemsRow.parentid = paymentsGrid.newRowPrefix + paymentsGrid.getLastNewRowPrefixId();
    paymentsItemsGrid.addRow(undefined, paymentsItemsRow);
    paymentsItemsGrid.setSummary();
}
pane.close();
EOT;
    }
    public static function synchronizePaymentOnClickAction(){
        return <<<EOT
var fields = ['customer', 'name', 'date', 'invoiceitemid', 'pricewt', 'paymenttype', 'reference', 'slip'],
    paymentsFields = {customer: 'parentid', name: 'name', date: 'date', paymenttype: 'paymenttype', pricewt: 'amount'},
    paymentsItemsFields = {name: 'name', pricewt: 'amount', invoiceitemid: 'invoiceitemid'}, pane = this.pane, form = pane.form,
    paymentsItemsGrid = form.getWidget('paymentsitems'), paymentsGrid = form.getWidget('payments'), fieldsValues = {}, paymentsItemsRow = {}, paymentsRow = {};
fields.forEach(function(field){
    fieldsValues[field] = pane.valueOf(field);
});
if (fieldsValues.paymenttype){
    utils.forEach(paymentsFields, function(paymentsField, field){
        paymentsRow[paymentsField] =  fieldsValues[field];
    });
    if (fieldsValues.paymenttype === 'paymenttype1'){
        ['reference', 'slip'].forEach(function(field){
            paymentsRow[field] = fieldsValues[field];
        });
    }
    paymentsGrid.addRow(undefined, paymentsRow);
    utils.forEach(paymentsItemsFields, function(paymentsItemsField, field){
        paymentsItemsRow[paymentsItemsField] =  fieldsValues[field];
    });
    paymentsItemsRow.parentid = paymentsGrid.newRowPrefix + paymentsGrid.getLastNewRowPrefixId();
    paymentsItemsGrid.addRow(undefined, paymentsItemsRow);
    paymentsItemsGrid.setSummary();
}
pane.close();
EOT;
    }
}
?>