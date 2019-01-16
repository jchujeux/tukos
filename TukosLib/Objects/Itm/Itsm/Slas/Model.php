<?php
namespace TukosLib\Objects\Itm\Itsm\Slas;

use TukosLib\Objects\Itm\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        
        $colsDefinition = [
            'deliverymgr'       => 'INT(11) DEFAULT NULL',
            'customerrep'       => 'INT(11) DEFAULT NULL',
            'startdate'         => 'date NULL DEFAULT NULL',
            'enddate'           => 'date NULL DEFAULT NULL',
            'itsystem'          => 'INT(11) DEFAULT NULL',
        ];
        $keysDefinition = ', KEY (`deliverymgr`, `customerrep`, `itsystem`)';

        parent::__construct(
            $objectName, $translator, 'itslas',
            ['parentid' => ['organizations'], 'deliverymgr' => ['people'], 'customerrep' => ['people'], 'itsystem' => ['itsystems']],
            [], $colsDefinition, $keysDefinition
        );
    }
}
?>
