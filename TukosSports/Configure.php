<?php 
namespace TukosSports; 

use TukosLib\AbstractConfigure;

class Configure extends AbstractConfigure{
    
    function __construct(){
        
        $modulesMenuLayout = [
            'admin' => [[
                '#users' => [['#customviews' => []]], '#contexts' => [], '#translations' => [],
                'mail' => [[
                    '#mailsmtps' => [], '#mailservers' => [], '#mailaccounts' => [],
                ]],
                '#scripts' => [['#scriptsoutputs' => []]], '#health' => [['#healthtables' => []]],
            ]],
            'collab' => [['#people' => [], '#organizations' => []/*, '#teams' => []*/, '#notes' => [], '#calendars' => [['#calendarsentries' => []]]]],
            'bustrack' => [
                ['#bustrackcategories' => [],
                    '#bustrackcatalog' => [], 'bustrackcustomers' => [['#bustrackpeople' => [], '#bustrackorganizations' => []]], '#bustrackquotes' => [],
                    'bustrackinvoices' => [['#bustrackinvoicescustomers' => [['#bustrackinvoicescustomersitems' => []]], '#bustrackinvoicessuppliers' => [['#bustrackinvoicessuppliersitems' => []]]]],
                    'bustrackpayments' => [['#bustrackpaymentscustomers' => [['#bustrackpaymentscustomersitems' => []]], '#bustrackpaymentssuppliers' => [['#bustrackpaymentssuppliersitems' => []]]]],
                    'bustrackreconciliations' => [['#bustrackreconciliationscustomers' => [], '#bustrackreconciliationssuppliers' => []]],
                    'bustrackdashboards' => [['#bustrackdashboardscustomers' => [], '#bustrackdashboardssuppliers' => []]]]
            ],
            'sports' => [['#sptathletes' => [], '#sptprograms' => [],  '#sptsessions' => [], '#sptsessionsstages' => [], '#sptexercises' => [['#sptexerciseslevels' => []]], '#sptequipments' => [], '#stravaactivities' => []]],
            'physio' => [['#physiopatients' => [], 'physiopersotrack' => [['#physiopersoquotes' => [], '#physiopersoplans' => [], '#physiopersotreatments' => [], '#physiopersodailies' => [['#physiopersosessions' => []]], '#physiopersoexercises' => []]], '#physioprescriptions' => [],
                'physiowoundtrack' => [['#physiogameplans' => [], '#physiogametracks' => []]],
                '#physioassesments' => [], '#physiocdcs' => []/*, '#physiotemplates' => []*/]],
            //'help' => ['type' => 'MenuBarItem', 'atts' => ['onclick' => 'tukos.Pmg.viewTranslatedInBrowserWindow("tukosSportsTukosTooltip", "Tukoslib");', 'style' => ['fontStyle' => 'italic']]]
        ];
        
        $accordion = [
            ['object' => 'users'     , 'view' => 'Pane', 'action' => 'Accordion', 'pane' => 'userContext'],
            ['object' => 'calendars' , 'view' => 'Edit', 'action' => 'Accordion', 'pane' => 'calendar', 'title' => 'calendar', 'config' => ['hasId' => true, 'hasCustomViewId'=> true]],
            ['object' => 'tukos'     , 'view' => 'Overview', 'action' => 'Accordion', 'pane' => 'search', 'title' => 'search', 'config' => ['hasCustomViewId' => true]],
            ['object' => 'users'     , 'view' => 'Pane', 'action' => 'Accordion', 'pane' => 'log'],
            ['object' => 'navigation', 'view' => 'Pane', 'action' => 'Accordion', 'pane' => 'navigationTree'],
        ];
        
        parent::__construct($modulesMenuLayout, [], $accordion);
    }
}
AbstractConfigure::__initialize();
?>
