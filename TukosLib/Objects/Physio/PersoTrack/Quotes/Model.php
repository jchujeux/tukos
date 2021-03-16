<?php
namespace TukosLib\Objects\Physio\PersoTrack\Quotes;

use TukosLib\Objects\BusTrack\Quotes\Model as QuotesModel;
use TukosLib\TukosFramework as Tfk;

class Model extends QuotesModel {
    function __construct($objectName){
        $quotesView = Tfk::$registry->get('objectsStore')->objectView('bustrackquotes');
        parent::__construct($objectName, $quotesView->tr);
    }
}
?>
