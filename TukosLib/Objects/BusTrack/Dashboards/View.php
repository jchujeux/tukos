<?php
namespace TukosLib\Objects\BusTrack\Dashboards;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
 
class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        $tr= $this->tr;
        $customDataWidgets = [
            'comments' => ['atts' => ['edit' => ['height' => '100px']]],
            'startdate' => ViewUtils::tukosDateBox($this, 'Periodstart'),
            'enddate' => ViewUtils::tukosDateBox($this, 'Periodend'),
            'paymentscount'  => ViewUtils::textBox($this, 'Paymentscount', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '4em']]]]),
            'paidvatfree'  => ViewUtils::tukosCurrencyBox($this, 'Paidvatfree', ['atts' => ['edit' => ['disabled' => true]]]),
            'paidwithvatwot'  => ViewUtils::tukosCurrencyBox($this, 'Paidwithvatwot', ['atts' => ['edit' => ['disabled' => true]]]),
            'paidvat'  => ViewUtils::tukosCurrencyBox($this, 'Paidvat', ['atts' => ['edit' => ['disabled' => true]]]),
            'paidwot'  => ViewUtils::tukosCurrencyBox($this, 'Paidwot', ['atts' => ['edit' => ['disabled' => true]]]),
            'paidwt'  => ViewUtils::tukosCurrencyBox($this, 'Paidwt', ['atts' => ['edit' => ['disabled' => true]]]),
            'paidwotpercategory' => ['type' => 'pieChart', 'atts' => ['edit' => 
                ['title' => $this->tr('paidwotpercategory'), 'showTable' => 'yes', 'tableWidth' => '30%', 'tableAtts' => $this->tableAtts('category'), 'series' => ['thePlot' => ['value' => ['y' => 'amount', 'text' => 'category']]]]]],
            'paymentslog' => ViewUtils::basicGrid($this, 'Paymentsdetails', [
                'id' => ['label' => $tr('invoice'), 'width' => '60'], 'name' => ['label' => $tr('Description')], 'amount' => ['label' => $tr('Amount'), 'formatType' => 'currency'], 'date' => ['label' => $tr('Date'), 'formatType' => 'date'], 
                'paymenttype' => ['label' => $tr('Paymenttype')], 'paymentreference' => ['label' => $tr('PaymentReference')], 'slip' => ['label' => $tr('CheckSlipNumber')], 'vatfree' => ['label' => $tr('Vatfree'), 'formatType' => 'translate'], 
                'vatrate' => ['label' => $tr('Vatrate'), 'formatType' => 'percent'], 'lefttopay' => ['label' => $tr('Lefttopay'), 'formatType' => 'currency'], 'reference' => ['label' => $tr('Invoicereference')], 
                'invoicedate' => ['label' => $tr('InvoiceDate'), 'formatType' => 'date']
             ], ['atts' => ['edit' => ['objectIdCols' => ['id']]]])
        ];
        $subObjects['bustrackinvoices'] = [
            'atts' => ['title' => $this->tr('pendinginvoices'), 'storeType' => 'LazyMemoryTreeObjects'],
            'filters' => ['organization' => '@parentid', [['col' => 'lefttopay', 'opr' => '>', 'values' => '0.00']]],
            'allDescendants' => 'hasChildrenOnly'
        ];
        
        $this->customize($customDataWidgets, $subObjects);
    }
    
    function tableAtts($description){
    	return ['maxHeight' => '300px', 'minWidth' => '160px', 'columns' => [
    	    $description => ['label' => $this->tr($description), 'field' => $description, 'width' => 130], 
    	    'amount' => ['label' => $this->tr('amount'), 'field' => 'amount', 'renderCell' => 'renderContent', 'formatType' => 'currency', 'width' => 70]
    	]];
    }


}
?>
