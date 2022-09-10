<?php
namespace TukosLib\Objects\Physio\WoundTrack;

use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;

trait IndicatorsConfig {
	
    
    public function setIndicatorsConfigActionWidget(){
        $tr = $this->view->tr;
        $this->actionWidgets['indicatorsconfig'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('IndicatorsConfig'), 'allowSave' => true]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'indicatorsconfig';
        $this->actionWidgets['indicatorsconfig']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'postElts' => ['indicators'], 
                'widgetsDescription' => [
                    'indicators' =>  Widgets::simpleDgrid(Widgets::complete(
                        ['label' => $tr('indicators'), 'storeArgs' => ['idProperty' => 'idg'],
                            'colsDescription' => [
                                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                                'description' => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('Description')], 'storeedit' => ['width' => 300]]), false),

                                /*'criteria' => Widgets::description(Widgets::storeComboBox([
                                    'edit' => ['label' => $tr('TrackingCriteria'), 'translations' => ['percentrecup' => $tr('percentrecup'), 'estimatedpain' => $tr('estimatedpain')],
                                        'storeArgs' => ['data' => [['id' => 'percentrecup', 'name' => $tr('percentrecup')],  ['id' => 'estimatedpain', 'name' => $tr('estimatedpain')]
                                        ]]],
                                    'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 150]]), false),*/
                                
                                //'criteria' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['percentrecup', 'estimatedpain'], $tr)], 'label' => $tr('TrackingCriteria')]]), false),
                                'minimum' => Widgets::description(Widgets::storeComboBox(['edit' => ['storeArgs' => ['data' => [['id' => 0, 'name' => 0]]], 'label' => $tr('Minimum')]]), false),
                                'maximum' => Widgets::description(Widgets::storeComboBox(['edit' => ['storeArgs' => ['data' => [['id' => 10, 'name' => 10], ['id' => 100, 'name' => 100]]], 'label' => $tr('Maximum')]]), false),
                                'tickinterval' => Widgets::description(Widgets::storeComboBox(['edit' => ['storeArgs' => ['data' => [['id' => 1, 'name' => 1], ['id' => 5, 'name' => 5], ['id' => 10, 'name' => 10], ['id' => 50, 'name' => 50], ['id' => 100, 'name' => 100]]], 'label' => $tr('Tickinterval')]]), false),
                                'snapinterval' => Widgets::description(Widgets::storeComboBox(['edit' => ['storeArgs' => ['data' => [['id' => 1, 'name' => 1], ['id' => 5, 'name' => 5], ['id' => 10, 'name' => 10]]], 'label' => $tr('Snapinterval')]]), false),
                                'showvalue' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['yes', 'no'], $tr)], 'label' => $tr('Showvalue')]]), false),
                                'ticklabel' => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('ticklabel'), 'style' => ['width' => '3em']], 'storeedit' => ['width' => 100]]), false),
                            ]])),
                    'apply' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('apply'), 'onClickAction' =>
                        "this.pane.form.localActions.indicatorsConfigApplyAction(this.pane);\n"
                    ]],
                    'cancel' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('close'), 'onClickAction' =>
                        "this.pane.close();\n"
                    ]],
                    
                ],
                'layout' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                    'contents' => [
                            'row1' => [
                                'tableAtts' =>['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                'widgets' => ['indicators'],
                            ],
                            'row2' => [
                                'tableAtts' =>['cols' => 2,  'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 150],
                                'widgets' => ['apply', 'cancel'],
                            ]
                    ]
                ],
                'onOpenAction' => $this->indicatorsConfigOnOpenAction()
            ],
        ];
    }
    protected function indicatorsConfigOnOpenAction(){
        return <<<EOT
const pane = this, form = pane.form;
let valueIndicators;
if (form.indicatorsConfig){
     valueIndicators = JSON.parse(form.indicatorsConfig.indicators);
}
pane.markIfChanged = false;
pane.setWidgets({value: {indicators: valueIndicators}});
pane.markIfChanged = true;
EOT;
    }
}
?>