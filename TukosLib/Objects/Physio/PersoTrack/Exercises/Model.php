<?php
namespace TukosLib\Objects\Physio\PersoTrack\Exercises;

use TukosLib\Objects\Sports\Exercises\Model as ExercisesModel;
use TukosLib\TukosFramework as Tfk;

class Model extends ExercisesModel {
    function __construct($objectName){
        $exercisesView = Tfk::$registry->get('objectsStore')->objectView('sptexercises');
        parent::__construct($objectName, $exercisesView->tr);
    }
}
?>
