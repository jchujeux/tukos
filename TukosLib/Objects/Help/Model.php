<?php
namespace TukosLib\Objects\Help;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    protected $languageOptions = ['en-us', 'fr-fr','es-es'];
    function __construct($objectName, $translator=null){
        $this->languageOptions = Tfk::$registry->get('appConfig')->languages['supported'];

        $colsDefinition = [
            'language' =>  "ENUM ('" . implode("','", $this->languageOptions) . "')",
        ];
        parent::__construct($objectName, $translator, 'help', ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects()], [], $colsDefinition, [['language']]);
    }
}
?>
