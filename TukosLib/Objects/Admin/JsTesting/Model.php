<?php
/**
 *
 * class for the scripts tukos object 
 */
namespace TukosLib\Objects\Admin\JsTesting;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk; 

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'tukosmodulepath' => 'VARCHAR(255)  DEFAULT NULL',
            'function'  => 'VARCHAR(80) DEFAULT NULL',
            'parameters'  => 'Longtext DEFAULT NULL',
            'outcome'  => 'Longtext DEFAULT NULL'
        ];
        parent::__construct($objectName, $translator, 'jstesting', ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects()], [], $colsDefinition, [], [], ['custom']);
    }
}
?>
