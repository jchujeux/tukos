<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Collab\Teams;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'leader'       =>  'INT(11) DEFAULT NULL',
            'emailcontact' =>  'VARCHAR(50) DEFAULT NULL',
            'telcontact'   =>  'VARCHAR(20) DEFAULT NULL',
        ];
        parent::__construct($objectName, $translator, 'teams', ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects(), 'leader' => ['people']], [], $colsDefinition);
    }   
}
?>
