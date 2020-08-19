<?php
namespace TukosLib\Objects\Bustrack\Payments\Suppliers;

use TukosLib\Objects\BusTrack\Payments\Customers\View as PaymentsView;

class View extends PaymentsView {
    protected $customerOrSupplier = 'Supplier';
    protected $paidOrPayingOrganization = 'Payingorganization';
    
}
?>
