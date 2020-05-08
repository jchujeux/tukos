<?php
namespace TukosLib\Objects\BusTrack\Invoices\Items;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'catalogid'     => "MEDIUMINT DEFAULT NULL",
            'quantity'      => "MEDIUMINT DEFAULT NULL",
            'unitpricewot' 	=> "DECIMAL (10, 2)",
        	'vatrate' 		=> "DECIMAL (5, 4)",
        	'unitpricewt'	=> "DECIMAL (10, 2)",
            'discount'      => "DECIMAL (5, 4)",
            'pricewot'      => "DECIMAL (10, 2)",
            'pricewt'       => "decimal (10, 2)",
            'category'      => "MEDIUMINT DEFAULT NULL",
            'vatfree'      => "VARCHAR(31) DEFAULT NULL"
        ];
        parent::__construct($objectName, $translator, 'bustrackinvoicesitems', ['parentid' => ['bustrackinvoices'], 'category' => ['bustrackcategories']], [], $colsDefinition, [], ['custom']);
        $this->invoicesIdsToProcess = [];
        $this->additionalColsForBulkDelete = ['parentid', 'pricewt'];
    }
    function initialize($init=[]){
    	return parent::initialize(array_merge(['vatrate' => 0.085], $init));
    }
    public function processInsertDeleteForBulk($values){
        $amountChanged = Utl::getItem('pricewt', $values, false, false);
        $invoiceId = Utl::getItem('parentid', $values);
        if ($this->bulkParentObject !== 'bustrackinvoices' && ($amountChanged || !empty($invoiceId))){
            $this->invoicesIdsToProcess = array_unique(array_merge($this->invoicesIdsToProcess, [$invoiceId]));
        }
        if (!$this->isBulkProcessing){
            $this->_postProcess();
        }
    }
    public function processUpdateForBulk($oldValues, $newValues){
        $amountChanged = Utl::getItem('pricewt', $newValues) != Utl::getItem('amount', $oldValues);
        $newInvoiceId = Utl::getItem('parentid', $newValues);
        $invoiceIdChanged =  $newInvoiceId != $oldInvoiceId = Utl::getItem('parentid', $oldValues);
        if ($this->bulkParentObject !== 'bustrackinvoices' && ($amountChanged || $invoiceIdChanged)){
            $this->invoicesIdsToProcess = array_unique(array_merge($this->invoicesIdsToProcess, array_filter($invoiceIdChanged ? [$oldInvoiceId, $newInvoiceId] : [$newInvoiceId])));
        }
        if (!$this->isBulkProcessing){
            $this->_postProcess();
        }
    }
    public function _postProcess(){
        if (!empty($this->invoicesIdsToProcess)){
            Tfk::$registry->get('objectsStore')->objectModel('bustrackinvoices')->updateForInvoicesItems($this->invoicesIdsToProcess);
            $this->invoicesIdsToProcess = [];
        }
    }
}
?>
