<?php
namespace TukosLib\Objects\Physio\PersoTrack\Exercises;

use TukosLib\Objects\AbstractView;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {
        
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        $exercisesView = Tfk::$registry->get('objectsStore')->objectView('sptexercises');
        $this->customize($exercisesView->customDataWidgets());
    }
}
?>
