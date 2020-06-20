<?php
namespace TukosLib\Objects\BusTrack\People;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\Collab\People\View as PeopleView;;

class View extends PeopleView {

	function __construct($objectName, $translator=null){
		AbstractView::__construct($objectName, $translator, 'Organization', 'Lastname');
        $customDataWidgets = $this->customDataWidgets();

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
