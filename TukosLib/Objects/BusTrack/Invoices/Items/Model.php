<?php
namespace TukosLib\Objects\BusTrack\Invoices\Items;

use TukosLib\Objects\AbstractModel;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'catalogid'     => "MEDIUMINT DEFAULT NULL",
            'quantity'      => "MEDIUMINT DEFAULT NULL",
            'unitpricewot' 	=> "DECIMAL (5, 2)",
        	'vatrate' 		=> "DECIMAL (5, 4)",
        	'unitpricewt'	=> "DECIMAL (5, 2)",
            'discount'      => "DECIMAL (5, 4)",
            'pricewot'      => "DECIMAL (5, 2)",
            'pricewt'       => "decimal (5, 2)",
            'category'      => "MEDIUMINT DEFAULT NULL",
            'vatfree'      => "VARCHAR(31) DEFAULT NULL"
        ];
        parent::__construct($objectName, $translator, 'bustrackinvoicesitems', ['parentid' => ['bustrackinvoices'], 'category' => ['bustrackcategories']], [], $colsDefinition, [], ['custom']);
    }
    function initialize($init=[]){
    	return parent::initialize(array_merge(['vatrate' => 0.085], $init));
    }
}
?>
