<?php
namespace TukosLib\Objects\BusTrack\Payments;

use TukosLib\Objects\AbstractModel;

class Model extends AbstractModel {
    
    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'date'        => 'VARCHAR(30)  DEFAULT NULL',
            'paymenttype' => "VARCHAR(255) DEFAULT NULL",
            'reference' => "VARCHAR(255) DEFAULT NULL",// for checks number
            'slip' => "VARCHAR(255) DEFAULT NULL",// for checks slip number
            'amount'   => "DECIMAL (5, 2)",
        ];
        parent::__construct($objectName, $translator, 'bustrackpayments', ['parentid' => ['bustrackpeople', 'bustrackorganizations']], [], $colsDefinition, [], ['worksheet', 'custom']);
    }
    function initialize($init=[]){
        return parent::initialize(array_merge(['date' => date('Y-m-d')], $init));
    }
}
?>