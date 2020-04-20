<?php
namespace TukosLib\Objects\BusTrack\Dashboards;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\BusTrack\Dashboards\ViewActionStrings as VAS;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        $tr= $this->tr;
        $customDataWidgets = [
            'comments' => ['atts' => ['edit' => ['height' => '100px']]],
            'startdate' => ViewUtils::tukosDateBox($this, 'Periodstart'),
            'enddate' => ViewUtils::tukosDateBox($this, 'Periodend'),
            'paymentsflag' => ViewUtils::checkBox($this, 'Paymentsflag', ['atts' => ['edit' => ['onWatchLocalAction' => VAS::flagLocalAction('paymentsflag')]]]),
            'pendinginvoicesflag' => ViewUtils::checkBox($this, 'Pendinginvoicesflag', ['atts' => ['edit' => ['onWatchLocalAction' => VAS::flagLocalAction('pendinginvoicesflag')]]]),
            'unassignedpaymentsflag' => ViewUtils::checkBox($this, 'Unassignedpaymentsflag', ['atts' => ['edit' => ['onWatchLocalAction' => VAS::flagLocalAction('unassignedpaymentsflag')]]]),
            'paymentscount'  => ViewUtils::textBox($this, 'Paymentscount', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '4em']]]]),
            'paidvatfree'  => ViewUtils::tukosCurrencyBox($this, 'Paidvatfree', ['atts' => ['edit' => ['disabled' => true]]]),
            'paidwithvatwot'  => ViewUtils::tukosCurrencyBox($this, 'Paidwithvatwot', ['atts' => ['edit' => ['disabled' => true]]]),
            'paidvat'  => ViewUtils::tukosCurrencyBox($this, 'Paidvat', ['atts' => ['edit' => ['disabled' => true]]]),
            'paidwot'  => ViewUtils::tukosCurrencyBox($this, 'Paidwot', ['atts' => ['edit' => ['disabled' => true]]]),
            'paidwt'  => ViewUtils::tukosCurrencyBox($this, 'Paidwt', ['atts' => ['edit' => ['disabled' => true]]]),
            'pendingamount'  => ViewUtils::tukosCurrencyBox($this, 'Pendingamount', ['atts' => ['edit' => ['disabled' => true]]]),
            'unassignedamount'  => ViewUtils::tukosCurrencyBox($this, 'Unassignedamount', ['atts' => ['edit' => ['disabled' => true]]]),
            'paidwotpercategory' => ['type' => 'pieChart', 'atts' => ['edit' => 
                ['title' => $this->tr('paidwotpercategory'), 'showTable' => 'yes', 'tableWidth' => '30%', 'tableAtts' => $this->tableAtts('category'), 'series' => ['thePlot' => ['value' => ['y' => 'amount', 'text' => 'category']]]]]],
            'paymentslog' => ViewUtils::basicGrid($this, 'Paymentsdetails', [
                'invoiceid' => ['label' => $tr('Invoice'), 'renderCell' => 'renderNamedId', 'width' => ''], 'customer' => ['label' => $tr('Customer'), 'renderCell' => 'renderNamedId', 'width' => '220'], 
                'invoicedate' => ['label' => $tr('InvoiceDate'), 'formatType' => 'date', 'width' => '100'], 'invoiceamount' => ['label' => $tr('Invoiceamount'), 'formatType' => 'currency', 'width' => '80'], 
                'paymentitemname' => ['label' => $tr('Paymentitem'), 'width' => '200'], 'paymentitemamount' => ['label' => $tr('Paidamount'), 'formatType' => 'currency', 'width' => '80'], 
                'vatfree' => ['label' => $tr('Vatfree'), 'formatType' => 'translate', 'width' => '70'], 'vatrate' => ['label' => $tr('Vatrate'), 'formatType' => 'percent', 'width' => '70'], 
                'paymentid' => ['label' => $tr('Payment'), 'width' => '70'], 'paymentdate' => ['label' => $tr('Date'), 'formatType' => 'date', 'width' => '100'], 'paymenttype' => ['label' => $tr('Paymenttype'), 'width' => '130'],
                'paymentreference' => ['label' => $tr('PaymentReference'), 'width' => '150'], 'slip' => ['label' => $tr('CheckSlipNumber'), 'width' => '150'],
            ], ['atts' => ['edit' => ['objectIdCols' => ['invoiceid', 'customer', 'paymentid']]]]),
            'pendinginvoiceslog' => ViewUtils::basicGrid($this, 'Pendinginvoices', [
                'id' => ['label' => $tr('Invoice'), 'renderCell' => 'renderNamedId'], 'customer' => ['label' => $tr('Customer'), 'renderCell' => 'renderNamedId'], 'name' => ['label' => $tr('Description')],
                'contact' => ['label' => $tr('Contact'), 'renderCell' => 'renderNamedId'], 'invoicedate' => ['label' => $tr('Invoicedate'), 'formatType' => 'date', 'width' => '100'], 
                'pricewt' => ['label' => $tr('Pricewt'), 'formatType' => 'currency', 'width' => '80'], 'lefttopay' => ['label' => $tr('Lefttopay'), 'formatType' => 'currency', 'width' => '80']
            ], ['atts' => ['edit' => ['objectIdCols' => ['id', 'parentid', 'contact']]]]),
            'unassignedpaymentslog' => ViewUtils::basicGrid($this, 'Unassignedpayments', [
                'id' => ['label' => $tr('Payment'), 'renderCell' => 'renderNamedId'], 'customer' => ['label' => $tr('Customer'), 'renderCell' => 'renderNamedId'], 'name' => ['label' => $tr('Description')],
                'date' => ['label' => $tr('Date'), 'formatType' => 'date', 'width' => '80'], 'paymenttype' => ['label' => $tr('Paymenttype')], 'amount' => ['label' => $tr('Amount'), 'formatType' => 'currency', 'width' => '80'], 
                'unassignedamount' => ['label' => $tr('Unassignedamount'), 'formatType' => 'currency', 'width' => '80']
            ], ['atts' => ['edit' => ['objectIdCols' => ['id', 'parentid']]]])
        ];
        $this->customize($customDataWidgets, [], ['grid' => ['paidwotpercategory', 'paymentslog', 'pendinginvoiceslog', 'unassignedpaymentslog']]);
    }
    
    function tableAtts($description){
    	return ['maxHeight' => '300px', 'minWidth' => '160px', 'columns' => [
    	    $description => ['label' => $this->tr($description), 'field' => $description, 'width' => 130], 
    	    'amount' => ['label' => $this->tr('amount'), 'field' => 'amount', 'renderCell' => 'renderContent', 'formatType' => 'currency', 'width' => 70]
    	]];
    }


}
?>
