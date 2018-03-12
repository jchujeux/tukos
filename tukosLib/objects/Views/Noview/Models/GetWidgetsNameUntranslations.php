<?php

namespace TukosLib\Objects\Views\Noview\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Objects\ObjectTranslator;
//use TukosLib\Objects\Directory;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class GetWidgetsNameUntranslations extends AbstractViewModel{
   function get($query){
        $untranslationsToGet = $this->dialogue->getValues();
        $translations = []; 
        $objectsStore     = Tfk::$registry->get('objectsStore');
        foreach ($untranslationsToGet as $objectName => $translatedWidgetsName){
            $translations[$objectName] = $this->objectsStore->objectView($objectName)->widgetsNameUntranslations($translatedWidgetsName);
        }
        return ['data' => $translations];
    }
}
?>

