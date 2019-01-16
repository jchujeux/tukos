<?php

namespace TukosLib\Objects\Views\NoView\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class GetTranslations extends AbstractViewModel{

   function get($query){
        $translationsToGet = $this->dialogue->getValues();
        $translations = []; 
        foreach ($translationsToGet as $objectName => $expressions){
            $translator = new ObjectTranslator($objectName);
            foreach ($expressions as $expression){
                $translations[$objectName][$expression] = $translator->tr($expression);
            }
        }
        return ['data' => $translations];
    }
}
?>
