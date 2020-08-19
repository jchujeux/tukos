<?php
namespace TukosLib\Objects\BusTrack\Invoices\Customers\Items;

use TukosLib\Objects\BusTrack\BusTrack;
use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\BusTrack\ViewActionStrings as VAS;

class View extends AbstractView {
    public $invoiceName = 'Invoice';
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, $this->invoiceName, 'Description');
        $this->sendOnSave = array_merge($this->sendOnSave, ['parentid']);
        $this->allowedNestedWatchActions = 0;
        $labels = Bustrack::$labels;
        $customDataWidgets = [
            'catalogid'    => ViewUtils::objectSelect($this, 'CatalogId', 'bustrackcatalog', ['atts' => ['edit' => [
                'storeArgs' => ['cols' => ['name', 'category', 'vatfree', 'vatrate', 'unitpricewot', 'unitpricewt']], 
                'onChangeLocalAction' => ['catalogid' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => false], 'action' => VAS::catalogIdLocalAction()]]]]]]),
            //'catalogid'    => ViewUtils::objectSelect($this, $labels['catalogid'], 'bustrackcatalog', ['atts' => ['storeedit' => ['width' => 100]]]),
            'category' => ViewUtils::ObjectSelect($this, 'Category', 'bustrackcategories', ['atts' => ['edit' => [
                'storeArgs' => ['cols' => ['vatfree']],
                'onWatchLocalAction' => ['value' => ['vatfree' => ['value' => ['triggers' => ['user' => true, 'server' => false], 'action' => "return sWidget.getItemProperty('vatfree') ? 'YES' : '';"]]]]
            ]]]),
            'vatfree' => ViewUtils::checkBox($this, 'vatfree', ['atts' => [
                'edit' => ['onWatchLocalAction' => [
                    'checked' => ['vatfree' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => true], 'action' => VAS::vatfreeLocalAction()]]]]],
                'storeedit' => ['editorArgs' => ['onWatchLocalAction' => [
                    'checked' => '~delete',
                    'value'   => ['vatfree' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => true], 'action' => VAS::vatfreeLocalAction()]]]]
            ]]]]),
            'quantity'  => ViewUtils::textBox($this, $labels['quantity'], ['atts' => [
                'edit' =>  ['style' => ['width' => '4em'], 'onChangeLocalAction' => ['quantity' => ['localActionStatus' =>
                    "var reduction = 1 - sWidget.valueOf('#discount'), priceWot = newValue * sWidget.valueOf('#unitpricewot') * reduction, priceWt = newValue * sWidget.valueOf('#unitpricewt') * reduction;\n" .
                    "sWidget.setValueOf('#pricewot', priceWot);\n" .
                    "sWidget.setValueOf('#pricewt', priceWt);\n" .
                    "return true;\n"
                ]]],
                'storeedit' => ['width' => 80]
            ]]),
            'unitpricewot'  => ViewUtils::tukosCurrencyBox($this, $labels['unitpricewot'], ['atts' => [
                'edit' =>  ['style' => ['width' => '4em'], 'onChangeLocalAction' => ['unitpricewot' => ['localActionStatus' =>
                    "var reduction = 1 -  sWidget.valueOf('#discount'), quantity = sWidget.valueOf('#quantity'), vatFactor = 1+Number(sWidget.valueOf('#vatrate')), newUnitPriceWt = newValue * vatFactor;\n" .
                    "sWidget.setValueOf('#unitpricewt', newUnitPriceWt);\n" .
                    "sWidget.setValueOf('#pricewot', quantity *  newValue * reduction);\n" .
                    "sWidget.setValueOf('#pricewt', quantity *  newUnitPriceWt * reduction);\n" .
                    "return true;\n"
                ]]],
                'storeedit' => ['formatType' => 'currency', 'width' => 80]
            ]]),
            'unitpricewt'  => ViewUtils::tukosCurrencyBox($this, $labels['unitpricewt'], ['atts' => [
                'edit' =>  ['style' => ['width' => '4em'], 'onChangeLocalAction' => ['unitpricewt' => ['localActionStatus' =>
                    "var reduction = 1 -  sWidget.valueOf('#discount'), quantity = sWidget.valueOf('#quantity'), vatFactor = 1+Number(sWidget.valueOf('#vatrate')), newUnitPriceWot = newValue / vatFactor;\n" .
                    "sWidget.setValueOf('#unitpricewot', newUnitPriceWot);\n" .
                    "sWidget.setValueOf('#pricewot', quantity *  newUnitPriceWot * reduction);\n" .
                    "sWidget.setValueOf('#pricewt', quantity *  newValue * reduction);\n" .
                    "return true;\n"
                ]]],
                'storeedit' => ['formatType' => 'currency', 'width' => 80]
            ]]),
            'discount' => ViewUtils::tukosNumberBox($this, $labels['discount'], ['atts' => [
                'edit' => ['title' => $this->tr($labels['discount']) . ' %', 'constraints' => ['type' => 'percent', 'pattern' => '#.####%'], 'editOptions' => ['pattern' => '#.####%'],
                    'onChangeLocalAction' => ['discount' => ['localActionStatus' =>
                        "var reduction = 1 -  newValue, quantity = sWidget.valueOf('#quantity'), unitPriceWot = sWidget.valueOf('#unitpricewot'), unitPriceWt = sWidget.valueOf('#unitpricewt');\n" .
                        "sWidget.setValueOf('#pricewot', quantity *  unitPriceWot * reduction);\n" .
                        "sWidget.setValueOf('#pricewt', quantity *  unitPriceWt * reduction);\n" .
                        "return true;\n"
                    ]]],
                'storeedit' => ['formatType' => 'percent', 'width' => 80]
            ]]),
            'pricewot'  => ViewUtils::tukosCurrencyBox($this, $labels['pricewot'], ['atts' => [
                'edit' => ['onChangeLocalAction' => ['pricewot' => ['localActionStatus' =>
                    "var quantity = sWidget.valueOf('#quantity'), unitPriceWot = sWidget.valueOf('#unitpricewot');\n" .
                    "sWidget.setValueOf('discount', (quantity && unitPriceWot) ? 1 - newValue / quantity / unitPriceWot : '');\n" .
                    "sWidget.setValueOf('pricewt', newValue * (1 + Number(sWidget.valueOf('#vatrate'))));\n" .
                    "return true;\n"
                ]]],
                'storeedit' => ['formatType' => 'currency', 'width' => 80]]]),
            'vatrate' => ViewUtils::tukosNumberBox($this, $labels['vatrate'], ['atts' => [
                'edit' => ['title' => $this->tr($labels['vatrate']) . ' %', 'constraints' => ['type' => 'percent', 'pattern' => '#.####%'], 'editOptions' => ['pattern' => '#.####%'],
                    'onWatchLocalAction' => ['value' => ['vatrate' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => true], 'action' => VAS::vatRateLocalAction()]]]]],
                'storeedit' => ['formatType' => 'percent', 'width' => 80],
                'overview' => ['formatType' => 'percent', 'width' => 80]
            ]]),
            'pricewt'  => ViewUtils::tukosCurrencyBox($this, $labels['pricewt'], ['atts' => [
                'edit' => ['onChangeLocalAction' => ['pricewt' => ['localActionStatus' =>
                    "var quantity = sWidget.valueOf('#quantity'), unitPriceWt = sWidget.valueOf('#unitpricewt'), vatFactor = 1 + Number(sWidget.valueOf('#vatrate'));\n" .
                    "sWidget.setValueOf('discount', (quantity && unitPriceWt) ? 1 - newValue / quantity / unitPriceWt : '');\n" .
                    "sWidget.setValueOf('pricewot', newValue / vatFactor);\n" .
                    "return true;\n"
                ]]],
                'storeedit' => ['formatType' => 'currency', 'width' => 80]]]),
            
        ];
        $this->customize($customDataWidgets);
    }    
}
?>
