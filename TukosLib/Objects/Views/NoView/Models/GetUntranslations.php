<?php

namespace TukosLib\Objects\Views\NoView\Models;

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
        if ($language = Utl::getItem('language', $query)){
            Tfk::$registry->get('translatorsStore')->setLanguage($language);
        }
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

