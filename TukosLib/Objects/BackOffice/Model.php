<?php
namespace TukosLib\Objects\BackOffice;

use TukosLib\TukosFramework as Tfk;
use TukosLib\Objects\ContentExporter;
use TukosLib\Utils\Translator;

class Model extends Translator{

    use ContentExporter;

    public static function translationSets(){
        return [];
    }
    function __construct($objectName){
        $this->objectName = $objectName;
        parent::__construct(Tfk::$tr);
    }
}
?>
