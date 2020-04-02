<?php
namespace TukosLib\Objects\BusTrack\Organizations;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;


class View extends AbstractView {

	function __construct($objectName, $translator=null){
		parent::__construct($objectName, $translator, 'Organization', 'Lastname');
		$customDataWidgets = [
		    'headofficeaddress' => ViewUtils::textArea($this, 'HeadOfficeAddress'),
		    'invoicingaddress' => ViewUtils::textArea($this, 'InvoicingAddress'),
		    'vatid' => ViewUtils::textBox($this, 'Vatid'),
		];
        $subObjects['bustrackquotes'] = [
            'atts'  => ['title' => $this->tr('bustrackquotes'),],
            'filters' => ['parentid' => '@id'],
            'allDescendants' => true,
        ];
        $subObjects['bustrackinvoices'] = [
            'atts' => ['title' => $this->tr('bustrackinvoices'), 'storeType' => 'LazyMemoryTreeObjects'],
            'filters' => ['parentid' => '@id'],
            'allDescendants' => 'hasChildrenOnly'
        ];
        $subObjects['bustrackpayments'] = [
            'atts' => ['title' => $this->tr('bustrackpayments'), 'storeType' => 'LazyMemoryTreeObjects'],
            'filters' => ['parentid' => '@id'],
            'allDescendants' => 'hasChildrenOnly'
        ];
        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>
