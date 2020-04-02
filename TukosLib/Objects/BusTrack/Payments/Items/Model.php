<?php
namespace TukosLib\Objects\BusTrack\Payments\Items;

use TukosLib\Objects\AbstractModel;

class Model extends AbstractModel {
    
    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'invoiceid' => "MEDIUMINT DEFAULT NULL",
            'invoiceitemid' => "MEDIUMINT DEFAULT NULL",
            'amount'   => "DECIMAL (5, 2)"
        ];
        parent::__construct($objectName, $translator, 'bustrackpaymentsitems', ['parentid' => ['bustrackpayments'], 'invoiceid' => ['bustrackinvoices'], 'invoiceitemid' => ['bustrackinvoicesitems']], [], $colsDefinition, [], ['worksheet', 'custom']);
    }
}
?>