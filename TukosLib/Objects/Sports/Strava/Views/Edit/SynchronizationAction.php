<?php
namespace TukosLib\Objects\Sports\Strava\Views\Edit;

use TukosLib\Utils\Widgets;

trait SynchronizationAction {
    
    public function setStravaActionButton($targetObject, $grid, $athlete, $coach, $synchrostart = 'synchrostart', $synchroend = 'synchroend', $daySortCol = 'sessionid', $dayDateCol = 'startdate', $sportsMapping = null){
        $view = $this->view;
        $model = $this->model;
        $tr = $view->tr;
        $view->addToTranslate(['bicycle', 'swimming', 'running', 'other', 'noneedtosync', 'needsstravaauthorization', 'isstravaauthorized', 'cannotsynchronizestrava', 'needtodefinecoach', 'needtodefineathlete']);
        $this->actionWidgets['stravasync'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $tr('stravasync'), 'allowSave' => true]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'stravasync';
        $this->actionWidgets['stravasync']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'widgetsDescription' => [
                        'ignoreitemflag' => Widgets::checkBox(Widgets::complete(['title' => $tr('ignoreitemflag'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('ignoreitemflag')])),
                    'synchrostreams' => Widgets::checkBox(Widgets::complete(['title' => $tr('synchrostreams'), 'checked' => true,  'onWatchLocalAction' => $this->watchCheckboxLocalAction('synchrostreams')])),
                    'synchroweatherstation' => Widgets::textBox(Widgets::complete(['title' => $tr('synchroweatherstation'), 'style' => ['width' => '5em'], 'onWatchLocalAction' => $this->watchLocalAction('synchroweatherstation')])),
                    'synchrostart' => Widgets::tukosDateBox(Widgets::complete(['title' => $tr('synchrostart'), 'onWatchLocalAction' => ['value' => [
                        'synchrostart' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->synchroStartLocalAction()]]]]])),
                    'synchroend' => Widgets::tukosDateBox(Widgets::complete(['title' => $tr('synchroend'), 'onWatchLocalAction' => ['value' => [
                        'synchroend' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "const toDate = dutils.parseDate(sWidget.pane.form.valueOf('todate')); return toDate < newValue ? toDate : newValue;"]]]]])),
                    'stlink' => Widgets::htmlContent(['title' => $tr('stlink'), 'readonly' => true]),
                    'stauthorize' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('stauthorize'), 'onClickAction' => 'this.pane.stravaLocalActions.authorizeStrava(this.pane);']],
                    'stsync' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('synchronize'), 'onClickAction' => 'this.pane.stravaLocalActions.synchronizeWithStrava(this.pane);']],
                    'close' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('close'), 'onClickAction' => "this.pane.close();\n"]],
                ],
                'layout' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                    'contents' => [
                        'row1' => [
                            'tableAtts' => ['cols' => 5,  'customClass' => 'labelsAndValues', 'label' => $tr('stsynchronization'), 'showLabels' => true, 'labelWidth' => 150],
                            'widgets' => ['ignoreitemflag', 'synchrostreams', 'synchroweatherstation', 'synchrostart', 'synchroend']
                        ],
                        'row2' => [
                            'tableAtts' =>['cols' => 2,  'customClass' => 'labelsAndValues', 'showLabels' => true],
                            'contents' => [
                                'col2' => [
                                    'tableAtts' => ['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => false],
                                    'contents' => [
                                        'row1' => [
                                            'tableAtts' => ['cols' => 2,  'customClass' => 'labelsAndValues', 'showLabels' => false],
                                            'contents' => [
                                                'col1' => ['tableAtts' => ['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => false], 'widgets' => ['stlink']],
                                                'col2' => ['tableAtts' => ['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => false], 'widgets' => ['stauthorize', 'stsync']]
                                            ]
                                        ],
                                    ],
                                ]
                            ],
                        ],
                        'row5' => [
                            'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                            'widgets' => ['close'],
                        ],
                    ],
                ],
                'onOpenAction' => $this->onOpenAction($view, $tr, $targetObject, $grid, $athlete, $coach, $synchrostart, $synchroend, $daySortCol, $dayDateCol, $sportsMapping)
        ]];
        if(!empty($synchroDefaultItemsCols = $model->itemsModel()->synchroDefaultItemsCols())){
            $itemsView = $model->itemsView();
            $this->actionWidgets['stravasync']['atts']['dialogDescription']['paneDescription']['defaultItemsCols'] = $synchroDefaultItemsCols;
            foreach($synchroDefaultItemsCols as $col){
                $this->actionWidgets['stravasync']['atts']['dialogDescription']['paneDescription']['widgetsDescription'][$col] = Widgets::description($itemsView->dataWidgets[$col], true);
                $this->actionWidgets['stravasync']['atts']['dialogDescription']['paneDescription']['layout']['contents']['row1']['widgets'][] = $col;
            }
        }
    }

    protected function _stravaHtmlContent($view, $tr, $targetObject, $grid, $athlete, $coach, $daySortCol, $dayDateCol, $sportsMapping){
            $sportsMappingString = json_encode($sportsMapping);
            return <<<EOT
const athleteId = this.form.valueOf("$athlete");
if (athleteId){
    const self = this;    
    Pmg.serverDialog({object: 'users', view: 'NoView', mode: 'Tab', action: 'Get', query: {params: {actionModel: 'GetItems'}}}, {data: utils.newObj([[athleteId, ['stravainfo']]])}).then(
    	function (response){
            var isStravaAuthorized = response.data[athleteId].stravainfo;
            self.getWidget('stlink').set('value', Pmg.message(isStravaAuthorized ? 'isstravaauthorized' : 'needsstravaauthorization', "{$view->objectName}"));
            self.getWidget('stsync').set('hidden', !isStravaAuthorized);
            self.getWidget('stauthorize').set('hidden', isStravaAuthorized);
            self.resize();
            require(["tukos/strava/LocalActions"], function(LocalActions){
                self.stravaLocalActions = new LocalActions({targetObject: "$targetObject", grid: "$grid", athlete: "$athlete", coach: "$coach", daySortCol: "$daySortCol", dayDateCol: "$dayDateCol", sportsMapping: $sportsMappingString});
            });
		}
   );
}else{
	Pmg.alert({title: Pmg.message('cannotsynchronizestrava', "{$view->objectName}"), content: Pmg.message('needtodefineathlete', "{$view->objectName}")});
    this.close();
}
EOT
            ;
        }
        protected function onOpenAction($view, $tr, $targetObject, $grid, $athlete, $coach, $synchrostart, $synchroend, $daySortCol, $dayDateCol, $sportsMapping){
            return <<<EOT
const synchroend = "$synchroend" ? this.form.valueOf("$synchroend") : dutils.formatDate(new Date()), synchrostart = "$synchrostart" ? this.form.valueOf("$synchrostart") : dutils.dateAdd(synchroend, 'day', -7);
this.setWidgets({value: {synchrostart: synchrostart, synchroend: synchroend}});
{$this->_stravaHtmlContent($view, $tr, $targetObject, $grid, $athlete, $coach, $daySortCol, $dayDateCol, $sportsMapping)}
EOT;
        }
    protected function synchroStartLocalAction(){
        return <<<EOT
const fromDate = dutils.parseDate(sWidget.pane.form.valueOf('fromdate'));
if (fromDate > newValue){
    Pmg.setFeedback(Pmg.message('synchrostartlowerthanfromdate'), null, null, true);
    return fromDate;
}else{
    return newValue;
}
EOT;
    }
}
?>