<?php 
namespace TukosMSQR; 

use TukosLib\AbstractConfigure;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class Configure extends AbstractConfigure{
    
    function __construct(){
        
        $modulesMenuLayout = function($tr){
            $isMobile = Tfk::$registry->isMobile;
            $notMobile = $isMobile ? 'no' : 'yes';
            $request =Tfk::$registry->request;
            $query = Tfk::$registry->urlQuery;
            $queryId = Utl::getItem('id', $query, '""');
            $newDailyPaneDescription = [
                'widgetsDescription' => [
                    'startdate' => ['type' => 'TukosDateBox', 'atts' => ['style' => ['width' => '6em'], 'value' => date('Y-m-d')]],
                    'ok' => ['type' => 'TukosButton', 'atts' => ['label' => Tfk::tr('Ok'), 'onClickAction' => <<<EOT
    var date = this.form.valueOf('startdate');
    Pmg.tabs.request({object: 'physiopersodailies', view: 'Edit', mode: 'Tab', action: 'Tab', query: {storeatts: {where: {startdate: date, parentid: {$queryId}}, init: {startdate: date, parentid: {$queryId}}}}});
    if ('$notMobile' == 'yes'){
        this.form.close();
        dijit.popup.close(dijit.getEnclosingWidget(this.form));
    }
    EOT
                ]]
                ],
                'layout' => ['tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false], 'widgets' => ['startdate', 'ok']]
            ];
            return array_merge(($request['object'] === 'physiopersotreatments' && $request['view'] === 'Edit' && isset($query['id']))
                ? ['@physiopersoplans' => ['type' => 'MenuBarItem', 'atts' => ['onClickArgs' => ['object' => 'physiopersoplans', 'view' => 'Edit', 'mode' => 'Tab', 'action' => 'Tab', 'query' => ['id' => ['object' => 'physiopersotreatments', 'id' => $queryId, 'col' => 'parentid']]]]],
                    '@physiopersotreatments' => ['type' => 'MenuBarItem', 'atts' => ['onClickArgs' => ['object' => 'physiopersotreatments', 'view' => 'Edit', 'mode' => 'Tab', 'action' => 'Tab', 'query' => ['id' => $queryId]]]]
                ]
                : [],
                ['@physiopersodailies' => [[
                    'new' => ['type' => 'PopupMenuItem', 'atts' => ['label' => Tfk::tr('new')], 'popup' => ['type' => $isMobile ? 'MobileTukosPane' : 'TukosTooltipDialog', 'atts' => $isMobile ? $newDailyPaneDescription :  ['paneDescription' => $newDailyPaneDescription]]],
                    'edit' => ['type' => 'PopupMenuItem', 'atts' => ['label' => Tfk::tr('edit')],
                        'popup' => Widgets::objectSelect(['placeHolder' => Tfk::tr('selectanitem'), 'onChangeArgs' => ['object' => 'physiopersodailies', 'view' => 'edit', 'mode' => 'Tab', 'action' => 'Tab'], 'object' => 'physiopersodailies', 'mode' => 'Tab'], true)],
                    'overview' => ['type' => 'MenuItem', 'atts' => ['onClickArgs' => ['object' => 'physiopersodailies', 'view' => 'Overview', 'mode' => 'Tab', 'action' => 'Tab'], 'label' => Tfk::tr('overview')]],
                ]],
                    'help' => ['type' => 'MenuBarItem', 'atts' => ['onclick' => 'tukos.Pmg.viewTranslatedInBrowserWindow("tukosMSQRTukosTooltip", "Tukoslib");', 'style' => ['fontStyle' => 'italic']]]
                ]
            );
        };

        parent::__construct('tukos20', $modulesMenuLayout, ['users', 'people', 'organizations', 'physiopatients', 'physiopersoplans', 'physiopersotreatments'], []);
        
    }
}
AbstractConfigure::__initialize();
?>
