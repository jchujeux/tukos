<?php
namespace TukosLib\Objects\BusTrack\Payments;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

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
                'storeedit' => ['formatType' => 'currency', 'width' => 80]]])
        ];
        $subObjects['items'] = ['object' => 'bustrackpaymentsitems', 'filters' => ['parentid' => '@id'], 'allDescendants' => true, 'atts' => ['title' => $this->tr('paymentItems'),
            'summaryRow' => ['cols' => [
                'name' => ['content' =>  ['Total']],
                'amount' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return Number(#amount#);"]]]
            ]],
            'onWatchLocalAction' => ['summary' => ['items' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' =>
                "sWidget.form.setValueOf('amount', sWidget.summary.amount);\n" .
                "return true;"
            ]]]]]];
        $this->customize($customDataWidgets, $subObjects);
    }
}
?>
