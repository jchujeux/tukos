<?php

namespace TukosLib\Objects\Views\NoView\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class GetWidgetsNameTranslations extends AbstractViewModel{
   function get($query){
        $translationsToGet = $this->dialogue->getValues();
        $translations = []; 
        $objectsStore     = Tfk::$registry->get('objectsStore');
        foreach ($translationsToGet as $objectName => $widgetsName){
            $translations[$objectName] = $this->objectsStore->objectView($objectName)->widgetsNameTranslations($widgetsName);
        }
        return ['data' => $translations];
    }
}
?>
