<?php
namespace TukosLib\Objects\BusTrack\Payments\Customers;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {
    protected $customerOrSupplier = 'Customer';
    protected $paidOrPayingOrganization = 'Paidorganization';
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, $this->customerOrSupplier, 'Description');
        $customersOrSuppliers = $this->model->customersOrSuppliers;
        $customDataWidgets = [
            'date' => ViewUtils::tukosDateBox($this, 'date', ['atts' => ['storeedit' => ['formatType' => 'date'], 'overview' => ['formatType' => 'date']]]),
            'paymenttype' => ViewUtils::StoreSelect('paymentType', $this, 'paymenttype', null, ['atts' => ['edit' => ['onChangeLocalAction' => [
                'reference' => ['hidden' => "return newValue !== 'paymenttype1';"],
                'slip' => ['hidden' => "return newValue !== 'paymenttype1';"]
            ]]]]),
            'reference' =>  ViewUtils::textBox($this, 'Paymentreference'),
            'slip' =>  ViewUtils::textBox($this, 'CheckSlipNumber'),
            'amount'  => ViewUtils::tukosCurrencyBox($this, 'Amount', ['atts' => [
                'edit' => ['onChangeLocalAction' => ['amount' => ['localActionStatus' => "sWidget.setValueOf('unassignedamount', newValue -  ((sWidget.form.getWidget('items').get('summary') || {}).amount || 0));return true;"]]],
                'storeedit' => ['formatType' => 'currency', 'width' => 80, 'editorArgs' => ['onChangeLocalAction' => ['amount' => ['localActionStatus' => 'return true;']]]],
                'overview' => ['formatType' => 'currency', 'width' => 80],
            ]]),
            'unassignedamount'  => ViewUtils::tukosCurrencyBox($this, 'UnassignedAmount', ['atts' => [
                'edit' => ['disabled' => true],
                'storeedit' => ['formatType' => 'currency', 'width' => 80],
                'overview' => ['hidden' => true]
            ]]),
            'isexplained' => ViewUtils::checkBox($this, 'Isexplained'),
            'category' => ViewUtils::ObjectSelect($this, 'unassignedCategory', 'bustrackcategories', ['atts' => ['edit' => [
                'dropdownFilters' => [["col" => "applyto{$customersOrSuppliers}", 'opr' => 'IN' , 'values' => ["YES", 1]]],
                'storeArgs' => ['cols' => ['vatfree']],
                'onWatchLocalAction' => ['value' => ['vatfree' => ['checked' => ['triggers' => ['user' => true, 'server' => false], 'action' => "return sWidget.getItemProperty('vatfree') ? true : false;"]]]]
            ]]]),
            'organization' => ViewUtils::objectSelect($this, $this->paidOrPayingOrganization, 'organizations'),
        ];
        $subObjects['items'] = ['object' => "bustrackpayments{$customersOrSuppliers}items", 'filters' => ['parentid' => '@id'], 'allDescendants' => true, 'atts' => ['title' => $this->tr("bustrackpayments{$customersOrSuppliers}items"),
            'summaryRow' => ['cols' => [
                'name' => ['content' =>  ['Total']],
                'amount' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return res + Number(#amount#);"]]]
            ]],
            'onWatchLocalAction' => ['summary' => ['items' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' =>
                "sWidget.form.setValueOf('unassignedamount', sWidget.form.valueOf('amount') - sWidget.summary.amount);\n" .
                "return true;"
            ]]]]]];
        $this->customize($customDataWidgets, $subObjects);
    }
}
?>
