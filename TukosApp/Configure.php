<?php 
namespace TukosApp; 
use TukosLib\AbstractConfigure;
use TukosLib\TukosFramework as Tfk;

class Configure extends AbstractConfigure{

    function __construct(){

        $modulesMenuLayout = [
            'admin' => [[
                '#users' => [['#customviews' => [], '#customwidgets' => []]], '#contexts' => [], '#objrelations' => [], '#translations' => [],
                'mail' => [[
                    '#mailsmtps' => [], '#mailservers' => [], '#mailaccounts' => [],
                ]],
                '#scripts' => [['#scriptsoutputs' => []]], '#health' => [],
            ]],
            'collab' => [['#people' => [], '#organizations' => [], '#teams' => [], '#notes' => [], '#documents' => [], '#calendars' => [['#calendarsentries' => []]], '#tasks' => [], '#blog' => []]],
            'bustrack' => [
                ['#bustrackcategories' => [],
                    '#bustrackcatalog' => [], 'bustrackcustomers' => [['#bustrackpeople' => [], '#bustrackorganizations' => []]], '#bustrackquotes' => [],
                    'bustrackinvoices' => [['#bustrackinvoicescustomers' => [['#bustrackinvoicescustomersitems' => []]], '#bustrackinvoicessuppliers' => [['#bustrackinvoicessuppliersitems' => []]]]],
                    'bustrackpayments' => [['#bustrackpaymentscustomers' => [['#bustrackpaymentscustomersitems' => []]], '#bustrackpaymentssuppliers' => [['#bustrackpaymentssuppliersitems' => []]]]],
                    'bustrackreconciliations' => [['#bustrackreconciliationscustomers' => [], '#bustrackreconciliationssuppliers' => []]],
                    'bustrackdashboards' => [['#bustrackdashboardscustomers' => [], '#bustrackdashboardssuppliers' => []]]]
            ],
            'wine' => [['#wines' => [['#wineappellations' => [], '#wineregions' => []]], '#winegrowers' => [], '#winecellars' => [['#wineinputs' => [], '#wineoutputs' => [], '#winestock' => [], '#winedashboards' => []]]]],
            'itm' => [['itsm' => [['#itsvcdescs' => [['#itslatargets' => []]], '#itincidents' => []]], '#itsystems' => [], '#networks' => [],
                '#hosts' => [['#macaddresses' => [], '#hostsdetails' => [], '#servicesdetails' => []]], '#connexions' => [],
            ]],
            'sports' => [['#sptathletes' => [], '#sptprograms' => [['#sptsessions' => [['#sptsessionsstages' => []]]]], '#sptplans' => [['#sptworkouts' => []]], '#sptexercises' => [['#sptexerciseslevels' => []]], '#stravaactivities' => [],]],
            'physio' => [['#physiopatients' => [], 'physiopersotrack' => [['#physiopersoquotes' => [], '#physiopersoplans' => [], '#physiopersotreatments' => [], '#physiopersodailies' => [['#physiopersosessions' => []]], '#physiopersoexercises' => []]],
                'physiowoundtrack' => [['#physiogameplans' => [], '#physiogametracks' => []]],
                '#physioprescriptions' => [], '#physioassesments' => [], '#physiocdcs' => [], '#physiotemplates' => []]],
            //'helptukosapp' => ['type' => 'MenuBarItem', 'atts' => ['onclick' => 'tukos.Pmg.viewTranslatedInBrowserWindow("tukosAppTukosTooltip", "Tukos");', 'style' => ['fontStyle' => 'italic']]]
        ];
        
        $accordion = [
            ['object' => 'users'     , 'view' => 'Pane', 'action' => 'Accordion', 'pane' => 'userContext'],
            ['object' => 'calendars' , 'view' => 'Edit', 'action' => 'Accordion', 'pane' => 'calendar', 'title' => 'calendar', 'config' => ['hasId' => true, 'hasCustomViewId'=> true]],
            ['object' => 'tukos'     , 'view' => 'Overview', 'action' => 'Accordion', 'pane' => 'search', 'title' => 'search', 'config' => ['hasCustomViewId' => true]],
            ['object' => 'users'     , 'view' => 'Pane', 'action' => 'Accordion', 'pane' => 'log'],
            ['object' => 'navigation', 'view' => 'Pane', 'action' => 'Accordion', 'pane' => 'navigationTree'],
        ];
        parent::__construct($modulesMenuLayout, [], $accordion);
        Tfk::$registry->headerHelpLink = $this->headerHelpTukosTooltipLink();
    }
}
AbstractConfigure::__initialize();
?>
