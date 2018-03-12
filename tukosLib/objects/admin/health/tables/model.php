<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Admin\Health\Tables;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    protected $backupOptions = ['NONE', 'FULL', 'INCREMENTAL'];
    function __construct($objectName, $translator=null){
        $colsDefinition = [ 'datehealthcheck'       =>  "timestamp",
                            'datepreviouscheck'     =>  "timestamp",
                            'countinsertedids'      =>  'INT(11)',
                            'countupdatedids'       =>  'INT(11)',
                            'countdeletedids'       =>  'INT(11)',
                            'backuptype'            =>  "ENUM ('" . implode("','", $this->backupOptions) . "') ",
                            'filename'              =>  'VARCHAR(255) DEFAULT NULL ',
                          ];
        parent::__construct(
            $objectName, $translator, 'healthtables',
            ['parentid' => ['users', 'scripts', 'health']], 
            [], $colsDefinition, ''
        );
    }   
}
?>
