<?php
namespace TukosLib\Objects\BusTrack\People;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

	function __construct($objectName, $translator=null){
		parent::__construct($objectName, $translator, 'Organization', 'Lastname');
        $customDataWidgets = [
            'firstname'  => ViewUtils::textBox($this, 'Firstname'),
            'title'      => ViewUtils::textBox($this, 'Civility'),
        	//'clientid'	 => ViewUtils::textBox($this, 'Clientid'),
            'email'      => ViewUtils::textBox($this, 'email', ['atts' => ['edit' =>  ['placeHolder' => 'xxx@yyy']]]),
            'telmobile'  => ViewUtils::textBox($this, 'Telmobile'),
            'street'     => ViewUtils::textBox($this, 'Streetaddress'),
            'postalcode' => ViewUtils::textBox($this, 'Postalcode'),
            'city'       => ViewUtils::textBox($this, 'City'),
            'country'    => ViewUtils::storeSelect('country', $this, 'Country'),
            'invoicingaddress'     => ViewUtils::textArea($this, 'Invoicingaddress'),
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
