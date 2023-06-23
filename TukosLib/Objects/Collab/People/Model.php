<?php
namespace TukosLib\Objects\Collab\People;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\CountryUtilities as CTU;

class Model extends AbstractModel {
    protected $countryOptions = CTU::alpha3Codes;

    public static function translationSets(){
        return ['countrycodes'];
    }

    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'title'     =>  'VARCHAR(80)  DEFAULT NULL',
            'firstname' =>  'VARCHAR(50)  DEFAULT NULL',
            'middlename'=>  'VARCHAR(50)  DEFAULT NULL',
            'email'     =>  'VARCHAR(50) DEFAULT NULL',
            'teloffice' =>  'VARCHAR(20) DEFAULT NULL',
            'telhome' =>  'VARCHAR(20) DEFAULT NULL',
            'telmobile' =>  'VARCHAR(20) DEFAULT NULL',
            'picture'   =>  'LONGBLOB DEFAULT NULL',
            'street' => 'VARCHAR(130)   DEFAULT NULL',
            'postalcode' => 'VARCHAR(20)   DEFAULT NULL',
            'city' => 'VARCHAR(80)   DEFAULT NULL',
            'country' => 'VARCHAR(80)   DEFAULT NULL',
            'postaladdress' => 'VARCHAR(255)   DEFAULT NULL',
            'invoicingaddress' => 'VARCHAR(255)   DEFAULT NULL',
            'birthdate' => 'date NULL DEFAULT NULL',
        	'sex'       =>  'VARCHAR(10) DEFAULT NULL ',
        	'socialsecuid' =>  'VARCHAR(20) DEFAULT NULL',
        	'profession'     =>  'VARCHAR(50) DEFAULT NULL',
        	'hobbies'     =>  'VARCHAR(255) DEFAULT NULL',
        	'maritalstatus'     =>  'VARCHAR(255) DEFAULT NULL',
        	'laterality' => 'VARCHAR(10) DEFAULT NULL ',
        	'height'    =>  'VARCHAR(10) DEFAULT NULL ',
        	'weight' =>  'VARCHAR(10) DEFAULT NULL ',
        	'corpulence' => 'VARCHAR(10) DEFAULT NULL ',
        	'morphotype' => 'VARCHAR(10) DEFAULT NULL ',
        	'antecedents' => 'longtext DEFAULT NULL',
            'hrmin' => 'SMALLINT DEFAULT NULL',
            'hrmax' => 'SMALLINT DEFAULT NULL',
            'hrthreshold' => 'SMALLINT DEFAULT NULL',
            'h4timethreshold' => 'SMALLINT DEFAULT NULL',
            'h5timethreshold' => 'SMALLINT DEFAULT NULL',
            'ftp' => 'SMALLINT DEFAULT NULL',
            'speedthreshold' => 'VARCHAR(10) DEFAULT NULL',
            'stravainfo' => 'VARCHAR(511) DEFAULT NULL',
        ];
        parent::__construct($objectName, $translator, 'people', ['parentid' => ['organizations']], [], $colsDefinition, [], ['country'], ['worksheet', 'custom'], ['name', 'firstname']);
    }    
}
?>
