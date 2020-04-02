<?php
namespace TukosLib\Objects\BusTrack\Payments\Items;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\BusTrack\BusTrack;

class View extends AbstractView {
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Payment', 'Description');
        $labels = Bustrack::$labels;
        $customDataWidgets = [
            'invoiceid'     => ViewUtils::objectSelect($this, $labels['invoice'], 'bustrackinvoices', ['atts' => ['storeedit' => ['width' => 100]]]),
            'invoiceitemid' => ViewUtils::objectSelect($this, $labels['invoiceitem'], 'bustrackinvoicesitems', ['atts' => ['storeedit' => ['width' => 100]]]),
            'amount'  => ViewUtils::tukosCurrencyBox($this, $labels['amount'], ['atts' => ['storeedit' => ['formatType' => 'currency', 'width' => 80]]])];
        $this->customize($customDataWidgets);
    }
}
?>
