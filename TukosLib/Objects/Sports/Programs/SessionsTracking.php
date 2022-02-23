<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;

trait SessionsTracking {
    
    
    public function setSessionsTrackingActionWidget($isSportsProgram = true, $sessionsWidget = 'sptsessions', $metricsToInclude = []){
        $tr = $this->view->tr;
        $this->actionWidgets['sessionstracking'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('Sessionstracking'), 'allowSave' => true, 'includeWidgets' => ['parentid', 'synchrostart', 'synchroend']]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'sessionstracking';
        $this->actionWidgets['sessionstracking']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'widgetsDescription' => array_merge(
                    $isSportsProgram ? [
                        'eventformurl' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showeventtrackingformurl'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('eventformurl')])),
                        'synchroflag' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('synchroflag'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('synchroflag')])),
                        'synchrosource' => Widgets::storeSelect(Widgets::complete(['storeArgs' => ['data' => Utl::idsNamesStore(['strava', 'goldencheetah'], $tr)], 'label' => $tr('synchrosource'), 'onWatchLocalAction' => ['value' => ['synchrosource' => ['localActionStatus' => ['action' => '']]]]])),
                        'formlogo' => Widgets::textBox(Widgets::complete(['label' => $tr('trackingformlogo'), 'style' => ['width' => '15em'], 'onWatchLocalAction' => $this->watchLocalAction('formlogo')])),
                        'formpresentation' => Widgets::storeSelect(Widgets::complete(['storeArgs' => ['data' => Utl::idsNamesStore(['MobileTextBox', 'default'], $tr)], 'label' => $tr('formpresentation'),
                            'onWatchLocalAction' => $this->watchLocalAction('formpresentation')])),
                        'version' => Widgets::storeSelect(Widgets::complete(['storeArgs' => ['data' => Utl::idsNamesStore(['V2'], $tr, [false, 'ucfirst', false])], 'label' => $tr('version'),
                            'value' => $this->view->model->defaultSessionsTrackingVersion, 'hidden' => true, 'onWatchLocalAction' => $this->watchLocalAction('version')])),
                    ] : [], [
                        'ignoresessionflag' => Widgets::checkBox(Widgets::complete(['title' => $tr('ignoresessionflag'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('ignoresessionflag')])),
                        'close' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('close'), 'onClickAction' => "this.pane.close();\n"]],
                    ]),
                'layout' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                    'contents' => array_merge(
                        $isSportsProgram ? [
                            'row1' => [
                                'tableAtts' =>['cols' => 6,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 150],
                                'widgets' => ['eventformurl', 'synchroflag', 'synchrosource', 'formlogo', 'formpresentation', 'version'],
                            ]] : [], [
                                'headerRow' => [],
                                'row2' => [
                                    'tableAtts' => ['cols' => 4,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 150],
                                    'widgets' => ['ignoresessionflag']
                                ],
                                'row3' => [],
                                'row4' => [],
                                'row5' => [
                                    'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                                    'widgets' => ['close'],
                                ],
                            ]),
                ],
                'onOpenAction' => $this->onOpenAction(),
        ]];
        $GCTracking = new GCSessionsTracking($this);
        $GCTracking->update($this->actionWidgets['sessionstracking']['atts']['dialogDescription']['paneDescription'], $isSportsProgram, $sessionsWidget, $metricsToInclude);
        $STTracking = new StravaSessionsTracking($this);
        $STTracking->update($this->actionWidgets['sessionstracking']['atts']['dialogDescription']['paneDescription'], $isSportsProgram, $sessionsWidget, $metricsToInclude);
        $this->actionWidgets['sessionstracking']['atts']['dialogDescription']['paneDescription']['onOpenAction'] .= 'pane.resize();';
        $this->actionWidgets['sessionstracking']['atts']['dialogDescription']['paneDescription']['widgetsDescription']['synchrosource']['atts']['onWatchLocalAction']['value']['synchrosource']['localActionStatus']['action'] .= 
            'sWidget.pane.resize();' . $this->watchLocalAction('synchrosource')['value']['synchrosource']['localActionStatus']['action'];
    }
    protected function onOpenAction(){
        return <<<EOT
var form = this.form, pane = this, synchroSource = pane.valueOf('synchrosource');
EOT;
    }
}
?>