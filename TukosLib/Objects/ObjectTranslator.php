<?php
namespace TukosLib\Objects;

use TukosLib\Utils\Translator;

use TukosLib\TukosFramework as Tfk;

class ObjectTranslator extends Translator{
    function __construct($objectName, $translator=null, $translateOrUntranslate = 'translator'){
        if (!$translator){
            $translatorsStore = Tfk::$registry->get('translatorsStore');
            $domainTranslatorName = Directory::getObjDomain($objectName);
            $modelClass = 'TukosLib\\Objects\\' . Directory::getObjDir($objectName) . '\\Model';
            $objectTranslationSets = $modelClass::translationSets();
            parent::__construct($translatorsStore->$translateOrUntranslate($objectName, $this->translationSetsPath = array_merge([Tfk::$registry->appName, $domainTranslatorName], $objectTranslationSets, ['tukosLib', 'common'])));
        }else{
            parent::__construct($translator);
        }
    }

}
?>
