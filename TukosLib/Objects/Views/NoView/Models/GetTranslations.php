<?php

namespace TukosLib\Objects\Views\NoView\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\TukosFramework as Tfk;

class GetTranslations extends AbstractViewModel{

   function get($query){
        $translationsToGet = $this->dialogue->getValues();
        $translations = []; 
        foreach ($translationsToGet as $objectName => $expressions){
            $translate = strtolower($objectName) === 'tukoslib' ? Tfk::$tr : (new ObjectTranslator($objectName))->tr;
            foreach ($expressions as $expression){
                $translations[$objectName][$expression] = $translate($expression);
            }
        }
        return ['data' => $translations];
    }
}
?>
