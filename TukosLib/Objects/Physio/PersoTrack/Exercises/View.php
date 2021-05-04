<?php
namespace TukosLib\Objects\Physio\PersoTrack\Exercises;

use TukosLib\Objects\AbstractView;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {
        
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        foreach(['level1', 'level2', 'level3'] as $widget){
            $this->dataWidgets[$widget]['atts']['edit']['hidden'] = true;
            $this->dataWidgets[$widget]['atts']['overview']['hidden'] = true;
            $this->dataWidgets[$widget]['atts']['storeedit']['hidden'] = true;
        }
        $exercisesView = Tfk::$registry->get('objectsStore')->objectView('sptexercises');
        $this->customize($exercisesView->customDataWidgets());
    }
}
?>
