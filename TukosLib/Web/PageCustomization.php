<?php
namespace TukosLib\Web;
use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as UTl;
use TukosLib\TukosFramework as Tfk;

trait PageCustomization{

    private function pageCustomDialogDescription($customValues){
        $tr = $this->tr;
        return [
            'title'   => $tr('pagecustomization'),
            'paneDescription' => [
                'id' => 'tukos_page_custom_dialog',
                'widgetsDescription' => [
                    'newPageCustom' => (Widgets::objectEditor(Widgets::complete(['title' => Tfk::tr('NewCustomContent'), 'keyToHtml' => 'capitalToBlank', 'onChangeLocalAction' => $this->newPageCustomAction()]))),
                    'tukosOrUser' => Widgets::storeSelect(widgets::complete(['storeArgs' => ['data' => Utl::idsNamesStore(['allusers', 'thisuser'], $tr)], 'title' => $tr('tukosOrUser'), 'onChangeLocalAction' => $this->tukosOrUserAction()])),
                    'pageCustomForAll' => Widgets::storeSelect(Widgets::complete(['storeArgs' =>['data' => Utl::idsNamesStore(['YES', 'NO'], $tr)], 'title' => $tr('pageCustomForAll'),
                        'onWatchLocalAction' => $this->watchLocalAction('pageCustomForAll')])),
                    'contextCustomForAll' => Widgets::storeSelect(Widgets::complete(['storeArgs' =>['data' => Utl::idsNamesStore(['YES', 'NO'], $tr)], 'title' => $tr('contextCustomForAll'),
                        'onWatchLocalAction' => $this->watchLocalAction('contextCustomForAll')])),
                    'defaultTukosUrls' => Widgets::simpleDgrid(Widgets::complete(['label' => $tr('defaultTukosUrls'), 'storeType' => 'MemoryTreeObjects', 'storeArgs' => ['idProperty' => 'idg'], 'initialId' => false, 'noDeleteRow' => true,
                        'style' => ['width' => '500px'], 'colsDescription' => [
                            //'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                            'app'  => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['TukosApp', 'TukosBus', 'TukosSports', 'TukosMSQR'], $tr)], 'label' => $tr('tukosAppName'),
                                'onWatchLocalAction' => $this->gridWatchLocalAction('defaultTukosUrls')]]), false),
                            'object'  => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore($this->user->allowedModules(), $tr)], 'label' => $tr('module'),
                                'onWatchLocalAction' => $this->gridWatchLocalAction('defaultTukosUrls')]]), false),
                            'view'  => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['edit', 'overview'], $tr)], 'label' => $tr('view'),
                                'onWatchLocalAction' => $this->gridWatchLocalAction('defaultTukosUrls')]]), false),
                            //'path' => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('defaultUrlPath'),
                            //    'onWatchLocalAction' => $this->gridWatchLocalAction('defaultTukosUrls')]]), false),
                            'query' => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('defaultUrlQuery'),
                                'onWatchLocalAction' => $this->gridWatchLocalAction('defaultTukosUrls')]]), false),
                        ],
                    ])),
                    'hideLeftPane' => Widgets::storeSelect(Widgets::complete(['storeArgs' =>['data' => Utl::idsNamesStore(['YES', 'NO'], $tr)], 'title' => $tr('hideleftpane'),'onWatchLocalAction' => $this->hideLeftPaneAction()])),
                    'leftPaneWidth' => Widgets::textBox(Widgets::complete(['label' => $tr('Leftpanewidth'), 'onWatchLocalAction' => $this->leftPaneWidthAction()])),
                    'panesConfig' => Widgets::simpleDgrid(Widgets::complete(['label' => $tr('panes'), 'storeType' => 'MemoryTreeObjects', 'storeArgs' => ['idProperty' => 'idg'], 'initialId' => false, 'style' => ['width' => '500px'],
                        'deleteRowAction' => $this->deleteRowAction('panesConfig'),    
                        'colsDescription' => [
                                //'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                                'name'  => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => $this->accordionStoreData], 'label' => $tr('panename'),
                                    'onWatchLocalAction' => $this->gridWatchLocalAction('panesConfig')]]), false),
                                'selectonopen' => Widgets::description(Widgets::checkBox(['edit' => ['label' => $tr('selectonopen'), 'onChangeLocalAction' => $this->selectedAction(),
                                    'onWatchLocalAction' => $this->gridWatchLocalAction('panesConfig')]]), false),
                                'present' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['YES', 'NO'], $tr)], 'label' => $tr('presentpane'),
                                    'onWatchLocalAction' => $this->gridWatchLocalAction('panesConfig')]]), false),
                                'associatedtukosid' => Widgets::description(Widgets::objectSelect([
                                    'edit' => ['label' => $tr('associatedtukosid'), 'object' => 'calendars', 'onWatchLocalAction' => $this->gridWatchLocalAction('panesConfig')],
                                    'storeedit' => ['canEdit' => '(function(item, cellValue){if(item.hasId){return true;}else{return false;}})']]), false),
                            ]])),
                    'fieldsMaxSize' => Widgets::textBox(Widgets::complete(['label' => $tr('Fieldsmaxsize'), 'onWatchLocalAction' => $this->watchLocalAction('fieldsMaxSize')])),
                    'historyMaxItems' => Widgets::textBox(Widgets::complete(['label' => $tr('HistoryMaxItems'), 'onWatchLocalAction' => $this->watchLocalAction('historyMaxItems')])),
                    'ignoreCustomOnClose' => Widgets::storeSelect(Widgets::complete(['storeArgs' => ['data' => Utl::idsNamesStore(['YES', 'NO'], $tr)], 'title' => $tr('ignoreCustomOnClose'), 
                        'onWatchLocalAction' => $this->watchLocalAction('ignoreCustomOnClose')])),
                    'cancel' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('close'), 'onClickAction' => 'this.pane.close();']],
                    'saveuser' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('saveforcurrentuser'), 'disabled' => true, 'onClickAction' => $this->saveOnClickAction('user')]],
                    'saveall' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('saveforallusers'), 'disabled' => true, 'onClickAction' => $this->saveOnClickAction('tukos')]],
                ],
                'layout' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                    'contents' => [
                        'row1' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                            'widgets' => ['newPageCustom'],
                        ],
                        'row2' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 250],
                            'widgets' => ['pageCustomForAll', 'contextCustomForAll', 'defaultTukosUrls', 'hideLeftPane', 'leftPaneWidth', 'panesConfig', 'fieldsMaxSize', 'historyMaxItems', 'ignoreCustomOnClose'],
                        ],
                        'row3' => [
                            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                            'contents' => [
                                'col1' => [
                                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 200],
                                    'widgets' => ['tukosOrUser'],
                                ],
                                'col2' => [
                                    'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                                    'widgets' => ['cancel', 'saveuser', 'saveall']
                                ]
                            ]
                        ]
                    ],
                ],
                'onOpenAction' => $this->fillDialogActionString('open')
            ],
        ];
    }
    private function watchLocalAction($widgetName){
        return [
            'value' => [$widgetName => ['localActionStatus' => ['triggers' => ['user' => true], 'action' =>
                "Pmg.addCustom('$widgetName', newValue);sWidget.setValueOf('newPageCustom',Pmg.getCustom({tukosOrUserOrChanges: 'changes'}));return true;"]]]
        ];
    }
    private function gridWatchLocalAction($widgetName){
        return [
            'value' => [$widgetName => ['localActionStatus' => ['triggers' => ['user' => true], 'action' => <<<EOT
var form = sWidget.parent.form, formGetWidget = lang.hitch(form, form.getWidget), grid = formGetWidget('$widgetName'); 
Pmg.addCustom('$widgetName', grid.dirty);
form.setValueOf('newPageCustom',Pmg.getCustom({tukosOrUserOrChanges: 'changes'}));return true;
EOT
            ]]]
        ];
    }
    private function deleteRowAction($widgetName){
        return <<<EOT
var form = this.form;
Pmg.addCustom('$widgetName', utils.newObj([[row.idg, '~delete']]));
form.setValueOf('newPageCustom',Pmg.getCustom({tukosOrUserOrChanges: 'changes'}));
EOT
        ;
    }
    private function fillDialogActionString($mode){
        return <<<EOT
var form = ('$mode' === 'open' ? this : sWidget.form), setValueOf = lang.hitch(form, form.setValueOf), 
    widgets = ['pageCustomForAll', 'contextCustomForAll', 'defaultTukosUrls', 'hideLeftPane', 'leftPaneWidth', 'panesConfig', 'fieldsMaxSize', 'historyMaxItems', 'ignoreCustomOnClose'];
form.watchOnChange = false;
form.markIfChanged = false;
form.emptyWidgets(widgets);
var disabled = ('$mode' === 'open' ? false : (newValue ? true : false));
widgets.forEach(function(widgetName){
    form.getWidget(widgetName).set('disabled', disabled);
});
utils.forEach(Pmg.getCustom('$mode' === 'open' || !newValue ? undefined : {tukosOrUserOrChanges: newValue === 'allusers' ? 'tukos' : 'user'}), function(value, widgetName){
    setValueOf(widgetName, value);
});
if ('$mode' === 'open'){
    if (Pmg.get('userRights')!== 'SUPERADMIN'){
        ['pageCustomForAll', 'contextCustomForAll', 'defaultTukosUrls', 'tukosOrUser', 'saveall'].forEach(function(widgetName){
            form.getWidget(widgetName).set('hidden', true);
        });
        form.resize();
    }
    form.watchOnChange = true;
    setValueOf('newPageCustom', Pmg.getCustom({tukosOrUserOrChanges: 'changes'}));
}
form.watchOnChange = true;
form.markIfChanged = true;
EOT;
    }
    private function newPageCustomAction(){
        return ['newPageCustom' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => <<<EOT
var pane = sWidget.pane, disabled = utils.empty(newValue) ? true : false;
['saveuser', 'saveall'].forEach(function(widgetName){
    pane.getWidget(widgetName).set('disabled', disabled);
});
sWidget.set('hidden', disabled); 
pane.resize();
EOT
        ]]];
    }
    private function tukosOrUserAction(){
        return ['tukosOrUser' => ['localActionStatus' => $this->fillDialogActionString('tukosOrUser')]];
    }
    private function hideLeftPaneAction(){
        return ['value' => ['hideLeftPane' => ['localActionStatus' => ['triggers' => ['user' => true], 'action' => <<<EOT
domstyle.set('leftPanel', 'display', (newValue === 'NO' ? 'block' : 'none'));
Pmg.addCustom('hideLeftPane', newValue);
sWidget.setValueOf('newPageCustom',Pmg.getCustom({tukosOrUserOrChanges: 'changes'}));
setTimeout(function(){registry.byId('appLayout').resize();}, 100);
return true;
EOT
        ]]]];
    }
    private function leftPaneWidthAction(){
        return ['value' => ['leftPaneWidth' => ['localActionStatus' => ['triggers' => ['user' => true], 'action' => <<<EOT
domstyle.set('leftPanel', 'width', newValue);
Pmg.addCustom('leftPaneWidth', newValue);
sWidget.setValueOf('newPageCustom',Pmg.getCustom({tukosOrUserOrChanges: 'changes'}));
setTimeout(function(){registry.byId('appLayout').resize();}, 100);
return true;
EOT
        ]]]];
    }
    private function selectedAction(){
        return ['selectonopen' => ['localActionStatus' => [<<<EOT
if (newValue){
    var grid = sWidget.grid, collection = grid.collection, idp = collection.idProperty, dirty = grid.dirty;
    collection.fetchSync().forEach(function(item){
        var idv = item[idp], dirtyItem = dirty[idv];
        if ((dirtyItem && dirtyItem.hasOwnProperty('selectonopen') && dirtyItem.selectonopen) || item.selectonopen){
            grid.updateDirty(idv, 'selectonopen', false);
        }
    })
}
return true;
EOT
        ]]];
    }
    private function saveOnClickAction($tukosOrUser){
        $saving = Tfk::tr('saving') . '...';
        return <<<EOT
var pane = this.pane, setValueOf = lang.hitch(pane, pane.setValueOf), panesConfigGrid = pane.getWidget('panesConfig'), data = Pmg.getCustom({tukosOrUserOrChanges: 'changes'});
Pmg.setFeedback('$saving');
Pmg.serverDialog({object: 'users', view: 'NoView', action: 'PageCustomSave', query: {tukosOrUser: '$tukosOrUser'}}, {data: data}).then(function(response){
    //Pmg.setCustom({'tukosOrUserOrChanges': '$tukosOrUser'}, lang.mixin(Pmg.getCustom({tukosOrUserOrChanges: '$tukosOrUser'}), Pmg.getCustom({tukosOrUserOrChanges: 'changes'})));    
    Pmg.setCustom({tukosOrUserOrChanges: '$tukosOrUser'}, response.pagecustom);    
    Pmg.setCustom({tukosOrUserOrChanges: '$tukosOrUser'}, lang.clone(response.pagecustom));    
    Pmg.setCustom({tukosOrUserOrChanges: 'changes'}, {});    
    setValueOf('newPageCustom', {});
    if (pane.valueOf('tukosOrUser') === '$tukosOrUser'){
        form.emptyWidgets(['pageCustomForAll', 'contextCustomForAll', 'defaultTukosUrls', 'hideLeftPane', 'leftPaneWidth', 'panesConfig', 'fieldsMaxSize', 'historyMaxItems', 'ignoreCustomOnClose']);
        utils.forEach(Pmg.getCustom({tukosOrUserOrChanges: '$tukosOrUser'}), function(value, widgetName){
            setValueOf(widgetName, value);
        });
    }
    pane.close();});
EOT
        ;
    }
}
?>