<?php 
namespace TukosBus; 

use TukosLib\AbstractConfigure;

class Configure extends AbstractConfigure{

    function __construct(){

        $modulesMenuLayout = [
            '#bustrackcategories' => [],
            '#bustrackcatalog' => [],
            'bustrackcustomers' => [['#bustrackpeople' => [], '#bustrackorganizations' => []]],
            '#bustrackquotes' => [],
            'bustrackinvoices' => [['#bustrackinvoicescustomers' => [['#bustrackinvoicescustomersitems' => []]], '#bustrackinvoicessuppliers' => [['#bustrackinvoicessuppliersitems' => []]]]],
            'bustrackpayments' => [['#bustrackpaymentscustomers' => [['#bustrackpaymentscustomersitems' => []]], '#bustrackpaymentssuppliers' => [['#bustrackpaymentssuppliersitems' => []]]]],
            'bustrackreconciliations' => [['#bustrackreconciliationscustomers' => [], '#bustrackreconciliationssuppliers' => []]],
            'bustrackdashboards' => [['#bustrackdashboardscustomers' => [], '#bustrackdashboardssuppliers' => []]],
            'help' => ['type' => 'MenuBarItem', 'atts' => ['onclick' => 'tukos.Pmg.viewTranslatedInBrowserWindow("tukosBusTukosTooltip", "Tukoslib");', 'style' => ['fontStyle' => 'italic']]]
        ];
        parent::__construct('tukos20', $modulesMenuLayout, ['users', 'people', 'organizations', 'physiopatients'], []);
        
    }
}
AbstractConfigure::__initialize();
?>
