<?php

namespace TukosLib\Objects\Views\Noview\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Objects\ObjectTranslator;
//use TukosLib\Objects\Directory;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class GetUntranslations extends AbstractViewModel{
   function get($query){
        $translationsToGet = $this->dialogue->getValues();
        $translations = []; 
        foreach ($translationsToGet as $objectName => $expressions){
            $translator = new ObjectTranslator($objectName, null, 'untranslator');
            foreach ($expressions as $translatedExpression){
                $translations[$objectName][$translator->tr($translatedExpression)] = $translatedExpression;
            }
        }
        return ['data' => $translations];
    }
}
?>

