<?php
namespace TukosLib\Objects\Itm\Systems;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Itm\Itm;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'citype' =>  "ENUM ('" . implode("','", Itm::$ciTypeOptions)   . "') ",
            'version'=>  "VARCHAR(255) DEFAULT '' ",
            'status' =>  "ENUM ('" . implode("','", Itm::$ciStatusOptions) . "') ",
        ];

        parent::__construct($objectName, $translator, 'itsystems', ['parentid' => ['itsystems', 'organizations', 'people']], [], $colsDefinition, [], ['citype', 'status']);
    }
}
?>
