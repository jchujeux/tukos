<?php
namespace TukosLib\Objects\BusTrack\Catalog;

use TukosLib\Objects\AbstractModel;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'unitpricewot' 	=> "DECIMAL (5, 2)",
        	'vatrate' 		=> "DECIMAL (5, 4)",
        	'unitpricewt'	=> "DECIMAL (5, 2)",
        ];
        parent::__construct($objectName, $translator, 'bustrackcatalog', ['parentid' => ['organizations']], [], $colsDefinition, [], ['worksheet', 'custom']);
    }
    function initialize($init=[]){
    	return parent::initialize(array_merge(['vatrate' => 0.085], $init));
    }
}
?>
