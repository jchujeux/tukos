<?php
namespace TukosLib\Objects\Modeling\Materials;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            "description" =>  "longtext DEFAULT NULL",
        ];
        parent::__construct($objectName, $translator, 'mdlmaterials', ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects()], ['description'], 
            $colsDefinition, [], [], ['custom']);
    }
}
?>
