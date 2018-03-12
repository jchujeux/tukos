<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Collab\Notes;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {


    function __construct($objectName, $translator=null){
        $colsDefinition = [];
        parent::__construct(
            $objectName, $translator, 'notes',
            ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects()],
            [], $colsDefinition, '', [], ['custom', 'worksheet']
        );
    }
}
?>
