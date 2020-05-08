<?php
namespace TukosLib\Objects\BusTrack\Catalog;

use TukosLib\Objects\AbstractModel;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'category'      => "MEDIUMINT DEFAULT NULL",
            'vatfree'      => "VARCHAR(31) DEFAULT NULL",
            'unitpricewot' 	=> "DECIMAL (10, 2)",
        	'vatrate' 		=> "DECIMAL (5, 4)",
        	'unitpricewt'	=> "DECIMAL (10, 2)",
        ];
        parent::__construct($objectName, $translator, 'bustrackcatalog', ['parentid' => ['organizations'], 'category' => ['bustrackcategories']], [], $colsDefinition, [], ['worksheet', 'custom']);
    }
    function initialize($init=[]){
    	return parent::initialize(array_merge(['vatrate' => 0.085], $init));
    }
}
?>
