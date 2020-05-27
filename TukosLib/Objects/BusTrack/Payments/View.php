<?php
namespace TukosLib\Objects\BusTrack\Payments;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\BusTrack\ViewActionStrings as VAS;

class View extends AbstractView {
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Customer', 'Description');
        $customDataWidgets = [
            'date' => ViewUtils::tukosDateBox($this, 'date', ['atts' => ['storeedit' => ['formatType' => 'date'], 'overview' => ['formatType' => 'date']]]),
            'paymenttype' => ViewUtils::StoreSelect('paymentType', $this, 'paymenttype', null, ['atts' => ['edit' => ['onChangeLocalAction' => [
                'reference' => ['hidden' => "return newValue !== 'paymenttype1';"],
                'slip' => ['hidden' => "return newValue !== 'paymenttype1';"]
            ]]]]),
            'reference' =>  ViewUtils::textBox($this, 'Paymentreference'),
            'slip' =>  ViewUtils::textBox($this, 'CheckSlipNumber'),
            'amount'  => ViewUtils::tukosCurrencyBox($this, 'Amount', ['atts' => [
                'edit' => ['onChangeLocalAction' => ['unassignedamount' => ['value' => "return newValue -  (sWidget.form.getWidget('items').get('summary') || 0);"]]],
                'storeedit' => ['formatType' => 'currency', 'width' => 80],
                'overview' => ['formatType' => 'currency', 'width' => 80],
            ]]),
            'unassignedamount'  => ViewUtils::tukosCurrencyBox($this, 'UnassignedAmount', ['atts' => [
                'edit' => ['disabled' => true],
                'storeedit' => ['formatType' => 'currency', 'width' => 80],
                'overview' => ['hidden' => true]
            ]]),
            'isexplained' => ViewUtils::checkBox($this, 'Isexplained'),
            'category' => ViewUtils::ObjectSelect($this, 'unassignedCategory', 'bustrackcategories', ['atts' => ['edit' => [
                'storeArgs' => ['cols' => ['vatfree']],
                'onWatchLocalAction' => ['value' => ['vatfree' => ['checked' => ['triggers' => ['user' => true, 'server' => false], 'action' => "return sWidget.getItemProperty('vatfree') ? true : false;"]]]]
            ]]]),
            'organization' => ViewUtils::objectSelect($this, 'Paidorganization', 'organizations'),
        ];
        $subObjects['items'] = ['object' => 'bustrackpaymentsitems', 'filters' => ['parentid' => '@id'], 'allDescendants' => true, 'atts' => ['title' => $this->tr('bustrackpaymentsitems'),
            'summaryRow' => ['cols' => [
                'name' => ['content' =>  ['Total']],
                'amount' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return Number(#amount#);"]]]
            ]],
            'onWatchLocalAction' => ['summary' => ['items' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' =>
                "sWidget.form.setValueOf('unassignedamount', sWidget.form.valueOf('amount') - sWidget.summary.amount);\n" .
                "return true;"
            ]]]]]];
        $this->customize($customDataWidgets, $subObjects);
    }
}
?>
