<?php
namespace TukosLib\Objects\BusTrack\Dashboards\Customers;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        $customersOrSuppliers = $this->model->customersOrSuppliers;
        $paidByOrTo = ['customers' => 'Customer', 'suppliers' => 'Supplier'][$customersOrSuppliers];
        $tr= $this->tr;
        $customDataWidgets = [
            'comments' => ['atts' => ['edit' => ['height' => '100px']]],
            'startdate' => ViewUtils::tukosDateBox($this, 'Periodstart'),
            'enddate' => ViewUtils::tukosDateBox($this, 'Periodend'),
            'startdatependinginvoices' => ViewUtils::tukosDateBox($this, 'Periodstartpendinginvoices'),
            'pendingamount'  => ViewUtils::tukosCurrencyBox($this, 'Pendingamount', ['atts' => ['edit' => ['disabled' => true]]]),
            'totalwotpercategory' => ['type' => 'pieChart', 'atts' => ['edit' => 
                ['title' => $this->tr('paidwotpercategory'), 'showTable' => 'yes', 'tableWidth' => '30%', 'tableAtts' => $this->tableAtts('category'), 'series' => ['thePlot' => ['value' => ['y' => 'amount', 'text' => 'category']]]]]],
            'paymentslog' => ViewUtils::basicGrid($this, 'Bustrackpayments', [
                'id' => ['label' => $tr('Payment'), 'renderCell' => 'renderNamedId'], 'customer' => ['label' => $tr('Paidby'), 'renderCell' => 'renderNamedId', 'width' => '200'],
                'date' => ['label' => $tr('Date'), 'formatType' => 'date', 'width' => '100'], 'paymenttype' => ['label' => $tr('Paymenttype'), 'width' => '150'], 'paymentreference' => ['label' => $tr('PaymentReference'), 'width' => '100'],
                'slip' => ['label' => $tr('CheckSlipNumber'), 'width' => '150'],'amount' => ['label' => $tr('Amount'), 'formatType' => 'currency', 'renderCell' => 'renderContent', 'width' => '100'],
                'unassignedamount' => ['label' => $tr('Unassignedamount'), 'formatType' => 'currency', 'renderCell' => 'renderContent', 'width' => '100'], 
                'category' => ['label' => $tr('Unassignedcategory'), 'renderCell' => 'renderNamedId', 'width' => '150'],
                'isexplained' => ['label' => $tr('Isexplained'), 'renderCell' => 'renderCheckBox', 'width' => 60],
            ], ['atts' => ['edit' => ['objectIdCols' => ['id', 'parentid'], 'maxHeight' => '550px', 'summaryRow' => ['cols' => [
                    'amount' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return res + Number(#amount#);"]]],
                    'unassignedamount' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return res + Number(#unassignedamount#);"]]]
                ]],
            ]]]),
            'pendinginvoiceslog' => ViewUtils::basicGrid($this, 'Pendinginvoices', [
                'id' => ['label' => $tr('Invoice'), 'renderCell' => 'renderNamedId'], 'customer' => ['label' => $tr($paidByOrTo), 'renderCell' => 'renderNamedId'], 'comments' => ['label' => $tr('Comments'), 'renderCell' => 'renderContent'],
                'contact' => ['label' => $tr('Contact'), 'renderCell' => 'renderNamedId'], 'invoicedate' => ['label' => $tr('Invoicedate'), 'formatType' => 'date', 'width' => '100'], 
                'pricewt' => ['label' => $tr('Pricewt'), 'formatType' => 'currency', 'renderCell' => 'renderContent', 'width' => '80'], 
                'lefttopay' => ['label' => $tr('Lefttopay'), 'formatType' => 'currency', 'renderCell' => 'renderContent', 'width' => '80']
            ], ['atts' => ['edit' => ['objectIdCols' => ['id', 'parentid', 'contact'], 'maxHeight' => '550px', 'summaryRow' => ['cols' => [
                'lefttopay' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return res + Number(#lefttopay#);"]]]
            ]]]]]),
            'paymentsdetailslog' => ViewUtils::basicGrid($this, 'Paymentsdetails', [
                'invoiceid' => ['label' => $tr('Invoice'), 'renderCell' => 'renderNamedIdExtra', 'width' => ''], 'customer' => ['label' => $tr($paidByOrTo), 'renderCell' => 'renderNamedId', 'width' => '220'],
                'invoicedate' => ['label' => $tr('InvoiceDate'), 'formatType' => 'date', 'width' => '100'], 'invoiceamount' => ['label' => $tr('Invoiceamount'), 'formatType' => 'currency', 'width' => '100'],
                'paymentitemname' => ['label' => $tr('Paymentitem'), 'width' => '200'], 'category' => ['label' => $tr('Category'), 'renderCell' => 'renderNamedId', 'width' => '200'], 
                'paymentitemamount' => ['label' => $tr('Paidamount'), 'formatType' => 'currency','renderCell' => 'renderContent', 'width' => '100'], 'vatfree' => ['label' => $tr('Vatfree'), 'formatType' => 'translate', 'width' => '100'], 
                'vatrate' => ['label' => $tr('Vatrate'), 'formatType' => 'percent', 'renderCell' => 'renderContent', 'width' => '100'],
                'paymentid' => ['label' => $tr('Payment'), 'width' => '100'], 'paymentdate' => ['label' => $tr('Date'), 'formatType' => 'date', 'width' => '100'],
            ], ['atts' => ['edit' => ['idProperty' => 'paymentitemid', 'objectIdCols' => ['invoiceid', 'customer', 'paymentid'], 'maxHeight' => '550px', 'summaryRow' => ['cols' => [
                'paymentitemamount' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return res + Number(#paymentitemamount#);"]]]
            ]]]]]),
        ];
        $noGrid = []; $colsLabels = []; $rowsLabels = [];
        foreach(['label', 'details', 'exp', 'unexp', 'total'] as $label){
            $noGrid[] = $widgetName = "label{$label}";
            $customDataWidgets[$widgetName] = ViewUtils::htmlContent($this, $colsLabels[$label] = ucfirst($widgetName), ['atts' => ['edit' => ['value' => $this->tr($widgetName), 'style' => ['textAlign' => 'center']]]]);
        }
        foreach(['vatfree', 'withvatwot', 'vat', 'wot', 'wt'] as $label){
            $noGrid[] = $widgetName = "label{$label}";
            $customDataWidgets[$widgetName] = ViewUtils::htmlContent($this, $rowsLabels[$label] = ucfirst($widgetName), ['atts' => ['edit' => ['value' => $this->tr($widgetName), 'style' => ['width' => '130px']]]]);
            foreach (['details', 'exp', 'unexp', 'total'] as $prefix){
                $widgetName = "{$prefix}{$label}";
                $customDataWidgets[$widgetName] = ViewUtils::tukosCurrencyBox($this, $widgetName, ['atts' => ['edit' => ['label' => "{$this->tr($colsLabels[$prefix])} {$this->tr($rowsLabels[$label])}", 'disabled' =>  true]]]);
            }
        }
        $this->doNotEmpty = $noGrid;
        $this->customize($customDataWidgets, [], ['grid' => array_merge($noGrid, ['paidwotpercategory', 'paymentslog', 'pendinginvoiceslog', 'unassignedpaymentslog'])]);
    }
    
    function tableAtts($description){
    	return ['maxHeight' => '300px', 'minWidth' => '160px', 'columns' => [
        	    $description => ['label' => $this->tr($description), 'field' => $description, 'width' => 130], 
        	    'amount' => ['label' => $this->tr('amount'), 'field' => 'amount', 'renderCell' => 'renderContent', 'formatType' => 'currency', 'width' => 70]
        	],
    	    'summaryRow' => ['cols' => ['amount' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return res + Number(#amount#);"]]]]]
    	];
    }


}
?>
