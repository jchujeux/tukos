<?php
namespace TukosLib\Objects\BusTrack\Reconciliations\Suppliers;

use TukosLib\Objects\BusTrack\Reconciliations\Customers\Model as ReconciliationsModel;

class Model extends ReconciliationsModel {
    
    public $customersOrSuppliers = 'suppliers';
    public $paymentsCol = 3;
    public $paymentIdentifiers = [1 => "CHEQUE EMIS", 2 => "PAIEMENT PAR CARTE", 3 => "VIREMENT EMIS", 4 => "VERSEMENT D'ESPECES", 5 => "other", 6 => "PRELEVEMENT",  7 =>  "COTISATION", 8 => "SOUSCRIPTION", 9 =>"REMBOURSEMENT DE PRET", 
        10 => "REGLEMENT", 11 => "FRAIS", 12 => "COMMISSION"];
    public $paymentTypeId = ["CHEQUE EMIS" => 1, "PAIEMENT PAR CARTE" => 2, "VIREMENT EMIS" => 3, "VERSEMENT D'ESPECES" => 4, "other" => 5, "PRELEVEMENT" => 6,  "COTISATION" => 6, "SOUSCRIPTION" => 6, "REMBOURSEMENT DE PRET" => 6,
        "REGLEMENT" => 6, "FRAIS" => 6, "COMMISSION" => 6];

}
?>
