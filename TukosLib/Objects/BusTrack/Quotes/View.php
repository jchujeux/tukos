<?php
namespace TukosLib\Objects\BusTrack\Quotes;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\BusTrack\QuotesAndInvoices;
use TukosLib\Objects\BusTrack\ViewActionStrings as VAS;

class View extends AbstractView {

    use QuotesAndInvoices;
    
	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Customer', 'Description');
		$this->allowedNestedWatchActions = 1;
        $this->customize($this->customDataWidgets(), $this->subObjects(), ['grid' => ['items']], ['items' => []]);
    }
    function customDataWidgets(){
        $labels = $this->model->itemsLabels;
        return [
            'reference' =>  ViewUtils::textBox($this, 'Reference', ['atts' => ['edit' => ['disabled' => true]]]),
            'quotedate' => ViewUtils::tukosDateBox($this, 'Quotedate'),
            'items'  => $this->items($labels),
            'discountpc' => $this->discountPc($labels),
            'discountwt' => $this->discountWt(),
            'pricewot' => $this->priceWot(),
            'pricewt' => $this->priceWt(),
            'downpay' => ViewUtils::tukosCurrencyBox($this, 'Downpay'),
            'status'   => ViewUtils::storeSelect('status', $this, 'Status'),
        ];
    }
    function subObjects(){
        return [
            'catalog' => [
                'object' => 'bustrackcatalog', 'filters' => [],
                'atts' => ['title' => $this->tr('Bustrackcatalog'), 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
                'allDescendants' => true,
            ]
        ];
    }
}
?>
