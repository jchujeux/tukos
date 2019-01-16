<?php
namespace TukosLib\Objects\BusTrack\Invoices;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\BusTrack\QuotesAndInvoices;
use TukosLib\Objects\ViewUtils;


class View extends AbstractView {

    use QuotesAndInvoices;
    
	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Customer', 'Description');
		$labels = $this->model->itemsLabels;
        $customDataWidgets = [
            'reference' =>  ViewUtils::textBox($this, 'Reference', ['atts' => ['edit' => ['disabled' => true]]]),
            'relatedquote' => ViewUtils::objectSelect($this, 'Relatedquote', 'bustrackquotes', [
            	'atts' => ['edit' => ['onChangeServerAction' => [
            		'inputWidgets' => ['relatedquote'],
            		'urlArgs' => ['query' => ['params' => json_encode(['getOne' => 'getQuoteChanged'])]],
            ]]]]),
        	'invoicedate' => ViewUtils::tukosDateBox($this, 'Invoicedate'),
            'items'  => $this->items($labels),
			'discountpc' => $this->discountPc($labels),
        	'discountwt' => $this->discountWt(),
			'pricewot' => $this->priceWot(),
			'pricewt' => $this->priceWt(),
        	'todeduce' => ViewUtils::tukosCurrencyBox($this, 'Todeduce'),
        	'status'   => ViewUtils::storeSelect('status', $this, 'Status'),
        ];

        $subObjects = [
            'catalog' => [
                'object' => 'bustrackcatalog', 'filters' => [], 
                'atts' => ['title' => $this->tr('Bustrackcatalog'), 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
                 'allDescendants' => true,
            ]
        ];
        $this->customize($customDataWidgets, $subObjects, ['grid' => ['items']], ['items' => []]);
    }    
}
?>
