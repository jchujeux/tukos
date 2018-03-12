<?php
namespace TukosLib\Objects\ITM\Systems;

use TukosLib\Objects\ITM\AbstractModel;
use TukosLib\Objects\ITM\ITM;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'citype' =>  "ENUM ('" . implode("','", ITM::$ciTypeOptions)   . "') ",
            'version'=>  "VARCHAR(255) DEFAULT '' ",
            'status' =>  "ENUM ('" . implode("','", ITM::$ciStatusOptions) . "') ",
        ];
        $keysDefinition = ' KEY (`citype`, `status`)';

        parent::__construct(
            $objectName, $translator, 'itsystems',
            ['parentid' => ['itsystems', 'organizations', 'people']], 
            [], $colsDefinition, $keysDefinition, ['citype', 'status']
        );
    }
}
?>
