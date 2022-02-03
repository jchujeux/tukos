<?php
namespace TukosLib\Objects\Collab\Organizations;

use TukosLib\Objects\AbstractModel;

class Model extends AbstractModel {
    protected $segmentOptions = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'MULTI'];
    protected $vatmodeOptions = ['normal', 'exemption'];
    function __construct($objectName, $translator=null){
    
        $colsDefinition = [
            'segment' =>  'VARCHAR(50)  DEFAULT NULL',
            'weburl' => 'VARCHAR(255) DEFAULT NULL',
            'trigram' =>  'CHAR(3)  DEFAULT NULL',
            'logo' =>  'longtext  DEFAULT NULL',
            'defaultfooter' =>  'longtext  DEFAULT NULL',
            'headofficeaddress' => 'VARCHAR(255) DEFAULT NULL',
            'invoicingaddress' => 'VARCHAR(255) DEFAULT NULL',
            'vatid' => 'VARCHAR(31) DEFAULT NULL',
            'vatmode' => 'VARCHAR(31) DEFAULT NULL',
            'legalid' => 'VARCHAR(31) DEFAULT NULL',
            'judicialform' => 'VARCHAR(31) DEFAULT NULL',
            'sharecapital' => 'VARCHAR(31) DEFAULT NULL'
        ];
        parent::__construct($objectName, $translator, 'organizations', ['parentid' => ['organizations']], [], $colsDefinition, [], ['segment']);
    }
}
?>
