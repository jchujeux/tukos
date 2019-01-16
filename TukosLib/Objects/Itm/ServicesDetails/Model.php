<?php
/**
 *
 * class for the HostsDetails tukos object, an "image" of the nmap hosts entity
 */
namespace TukosLib\Objects\Itm\ServicesDetails;

use TukosLib\Objects\AbstractModel;
use TukosLib\TukosFramework as Tfk; 

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition =  ['port'      => 'INT(11) NOT NULL',
                            'protocol'  => 'VARCHAR(80)  DEFAULT NULL',
                            'product'   => 'VARCHAR(80)  DEFAULT NULL',
                            'version'   => 'VARCHAR(80)  DEFAULT NULL',
                            'timescanned' => "timestamp NULL DEFAULT NULL",
                            ];
        parent::__construct($objectName, $translator, 'servicesdetails', ['parentid' => ['hosts']], [], $colsDefinition, '');
    }
}
?>
