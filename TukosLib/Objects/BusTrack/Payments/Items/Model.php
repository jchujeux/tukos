<?php
namespace TukosLib\Objects\BusTrack\Payments\Items;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    
    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'invoiceid' => "MEDIUMINT DEFAULT NULL",
            'invoiceitemid' => "MEDIUMINT DEFAULT NULL",
            'amount'   => "DECIMAL (10, 2)"
        ];
        parent::__construct($objectName, $translator, 'bustrackpaymentsitems', ['parentid' => ['bustrackpayments'], 'invoiceid' => ['bustrackinvoices'], 'invoiceitemid' => ['bustrackinvoicesitems']], [], $colsDefinition, [], ['worksheet', 'custom']);
        $this->invoicesIdsToProcess = [];
        $this->paymentsIdsToProcess = [];
        $this->additionalColsForBulkDelete = ['parentid', 'invoiceid', 'amount'];
    }
    public function processInsertDeleteForBulk($values){
        $amountChanged = Utl::getItem('amount', $values, false, false);
        $invoiceId = Utl::getItem('invoiceid', $values);
        $paymentId = Utl::getItem('parentid', $values);
        if ($this->bulkParentObject !== 'bustrackpayments' && ($amountChanged || !empty($paymentId))){
            $this->paymentsIdsToProcess = array_unique(array_merge($this->paymentsIdsToProcess, [$paymentId]));
        }
        if ($this->bulkParentObject !== 'bustrackinvoices' && ($amountChanged || !empty($invoiceId))){
            $this->invoicesIdsToProcess = array_unique(array_merge($this->invoicesIdsToProcess, [$invoiceId]));
        }
        if (!$this->isBulkProcessing){
            $this->_postProcess();
        }
    }
    public function processUpdateForBulk($oldValues, $newValues){
        $amountChanged = Utl::getItem('amount', $newValues) != Utl::getItem('amount', $oldValues);
        $newInvoiceId = Utl::getItem('invoiceid', $newValues);
        $invoiceIdChanged =  $newInvoiceId != $oldInvoiceId = Utl::getItem('invoiceid', $oldValues);
        $newPaymentId = Utl::getItem('parentid', $newValues);
        $paymentIdChanged = $newPaymentId != ($oldPaymentId = Utl::getItem('parentid', $oldValues));
        if ($this->bulkParentObject !== 'bustrackpayments' && ($amountChanged || $paymentIdChanged)){
            $this->paymentsIdsToProcess = array_unique(array_merge($this->paymentsIdsToProcess, array_filter($paymentIdChanged ? [$oldPaymentId, $newPaymentId] : [$newPaymentId])));
        }
        if ($this->bulkParentObject !== 'bustrackinvoices' && ($amountChanged || $invoiceIdChanged)){
            $this->invoicesIdsToProcess = array_unique(array_merge($this->invoicesIdsToProcess, array_filter($invoiceIdChanged ? [$oldInvoiceId, $newInvoiceId] : [$newInvoiceId])));
        }
        if (!$this->isBulkProcessing){
            $this->_postProcess();
        }
    }
    public function _postProcess(){
        if (!empty($this->paymentsIdsToProcess)){
            Tfk::$registry->get('objectsStore')->objectModel('bustrackpayments')->updatePayments($this->paymentsIdsToProcess);
            $this->paymentsIdsToProcess = [];
        }
        if (!empty($this->invoicesIdsToProcess)){
            Tfk::$registry->get('objectsStore')->objectModel('bustrackinvoices')->updateForPaymentsItems($this->invoicesIdsToProcess);
            $this->invoicesIdsToProcess = [];
        }
    }
}
?>