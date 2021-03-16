<?php
namespace TukosLib\Objects\Physio\PersoTrack\Quotes;

use TukosLib\Objects\AbstractView;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {
        
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Patient', 'Description');
        $quotesView = Tfk::$registry->get('objectsStore')->objectView('bustrackquotes');
        $this->customize($quotesView->customDataWidgets(), $quotesView->subObjects(), ['grid' => ['items']], ['items' => []]);
    }
}
?>
