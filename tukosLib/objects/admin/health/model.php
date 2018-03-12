<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Admin\Health;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition = [ 'datehealthcheck'       =>  "timestamp",
                            'datepreviouscheck'     =>  "timestamp",
                            'nextid'                =>  'INT(11)',
                            'countinsertedids'      =>  'INT(11)',
                            'countupdatedids'       =>  'INT(11)',
                            'countdeletedids'       =>  'INT(11)',
                            'countaffectedtables'   =>  'INT(11)',
                          ];
        parent::__construct($objectName, $translator, 'health', ['parentid' => ['users', 'scripts']], [], $colsDefinition, '');
    }   
}
?>
