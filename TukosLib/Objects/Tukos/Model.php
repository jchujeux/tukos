<?php
/**
 *
 * class for handling all objects as a whole
 */
namespace TukosLib\Objects\Tukos;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {


    function __construct($objectName, $translator=null){
        $colsDefinition = [];
        parent::__construct(
            $objectName, $translator, 'tukos',
            ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects()],
            [], $colsDefinition, '', ['object'], []
        );
    }
}
?>
