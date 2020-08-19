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
        $subObjects['bustrackinvoicescustomers'] = [
            'atts' => ['title' => $this->tr('bustrackinvoicescustomers'), 'storeType' => 'LazyMemoryTreeObjects'],
            'filters' => ['parentid' => '@id'],
            'allDescendants' => 'hasChildrenOnly'
        ];
        $subObjects['bustrackpaymentscustomers'] = [
            'atts' => ['title' => $this->tr('bustrackpaymentscustomers'), 'storeType' => 'LazyMemoryTreeObjects'],
            'filters' => ['parentid' => '@id'],
            'allDescendants' => 'hasChildrenOnly'
        ];
        $subObjects['bustrackinvoicessuppliers'] = [
            'atts' => ['title' => $this->tr('bustrackinvoicessuppliers'), 'storeType' => 'LazyMemoryTreeObjects'],
            'filters' => ['parentid' => '@id'],
            'allDescendants' => 'hasChildrenOnly'
        ];
        $subObjects['bustrackpaymentssuppliers'] = [
            'atts' => ['title' => $this->tr('bustrackpaymentssuppliers'), 'storeType' => 'LazyMemoryTreeObjects'],
            'filters' => ['parentid' => '@id'],
            'allDescendants' => 'hasChildrenOnly'
        ];
        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>
