<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\Widgets;
use TukosLib\Objects\Sports\Strava as ST;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class StravaSessionsTracking {
    
    
    function __construct($editView){
        $this->editView = $editView;
    }
    public function update(&$dialogDescription, $isSportsProgram,  $sessionsWidget, $metricsToInclude){
        $tr = $this->editView->view->tr;
        $view = $this->editView;
        $view->view->addToTranslate(['nomatch', 'newsession', 'synced', 'bicycle', 'swimming', 'running', 'other', 'noneedtosync', 'needsstravaauthorization', 'isstravaauthorized']);
        $stWidgets = [
            'synchrostreams' => Widgets::checkBox(Widgets::complete(['title' => $tr('synchrostreams'), 'onWatchLocalAction' => $this->editView->watchCheckboxLocalAction('synchrostreams')])),
            'stsynchrostart' => Widgets::tukosDateBox(['title' => $tr('synchrostart')]),
            'stsynchroend' => Widgets::tukosDateBox(['title' => $tr('synchroend')]),
            //'stmetricstoinclude' => Widgets::multiSelect(Widgets::complete(['title' => $tr('metricstoinclude'), 'options' => ST::metricsOptions($tr, $metricsToInclude), 'style' => ['height' => '150px'], 'onWatchLocalAction' =>  $view->watchLocalAction('stmetricstoinclude')])),
            'stlink' => Widgets::htmlContent(['title' => $tr('gclink'), 'readonly' => true]),
            'stauthorize' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('stauthorize'), 'onClickAction' => 'this.pane.form.localActions.authorizeStrava(this.pane);']],
            'stsync' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('synchronize'), 'onClickAction' => 'this.pane.form.localActions.synchronizeWithStrava(this.pane);']]
        ];
        $this->jsStWidgets = json_encode(array_keys($stWidgets));
        $dialogDescription['widgetsDescription'] = array_merge($dialogDescription['widgetsDescription'], $stWidgets);
        $this->updateSynchroSourceWatchAction($dialogDescription['widgetsDescription']['synchrosource']['atts']['onWatchLocalAction']['value']['synchrosource']['localActionStatus']['action'], $view, $tr);
        $dialogDescription['layout']['contents'] = Utl::array_merge_recursive_replace($dialogDescription['layout']['contents'], [
            'headerRow' => [
                'tableAtts' => ['cols' =>  1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert'],
                'contents' => [
                    'title' => [
                        'tableAtts' => ['cols' =>  1, 'customClass' => 'labelsAndValues', 'label' => $tr('stsynchronization')]
                    ]
                ]],
            'row2' => [
                'widgets' => ['synchrostreams', 'stsynchrostart', 'stsynchroend'],
            ],
            'row3' => [
                'tableAtts' =>['cols' => 2,  'customClass' => 'labelsAndValues', 'showLabels' => true/*, 'widgetWidths' => ['10%', '90%']*/],
                'contents' => [
                    //'col1' => ['tableAtts' =>['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 'widgets' => ['stmetricstoinclude']],
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
        ], false);
        $dialogDescription['onOpenAction'] .= $this->onOpenAction($view, $tr);
    }
    protected function _stravaHtmlContent($view, $tr){
        return <<<EOT
if (synchroSource === 'strava'){
	var parentId = pane.form.valueOf('parentid');
    Pmg.serverDialog({object: 'users', view: 'NoView', mode: 'Tab', action: 'Get', query: {params: {actionModel: 'GetItems'}}}, {data: utils.newObj([[parentId, ['stravainfo']]])}).then(
    	function (response){
            var isStravaAuthorized = response.data[parentId].stravainfo;
            pane.getWidget('stlink').set('value', Pmg.message(isStravaAuthorized ? 'isstravaauthorized' : 'needsstravaauthorization', "{$view->objectName}"));
            pane.getWidget('stsync').set('hidden', !isStravaAuthorized);
            pane.getWidget('stauthorize').set('hidden', isStravaAuthorized);
            pane.resize();
		}
	);	
}
EOT
        ;
    }
    protected function _stSynchroSourceActionString(){
        return <<<EOT
var isNotStrava = synchroSource !== 'strava';
pane.layout.contents.headerRow.tableAtts.showLabels = !isNotStrava;
{$this->jsStWidgets}.forEach(function(name){
    pane.getWidget(name).set('hidden', isNotStrava);
});
EOT
;
    }
    protected function onOpenAction($view, $tr){
        return <<<EOT
var isNotStrava = synchroSource !== 'strava';
pane.layout.contents.headerRow.tableAtts.showLabels = !isNotStrava;
{$this->jsStWidgets}.forEach(function(name){
    pane.getWidget(name).set('hidden', isNotStrava);
});
pane.setWidgets({value: {stsynchrostart: form.valueOf('synchrostart'), stsynchroend: form.valueOf('synchroend')}});
{$this->_stSynchroSourceActionString()}
{$this->_stravaHtmlContent($view, $tr)}
EOT;
    }
    protected function updateSynchroSourceWatchAction(&$theStringAction, $view, $tr){
        $theStringAction = "var synchroSource = sWidget.valueOf('synchrosource'), pane = sWidget.pane;" . $this->_stSynchroSourceActionString() . $this->_stravaHtmlContent($view, $tr) . $theStringAction;
    }
}
?>