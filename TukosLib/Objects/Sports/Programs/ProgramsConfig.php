<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;

trait ProgramsConfig {
	
    
    public function setProgramsConfigActionWidget(){
        $tr = $this->view->tr;
        $this->actionWidgets['programsconfig'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('ProgramsConfig'), 'allowSave' => true]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'programsconfig';
        $this->actionWidgets['programsconfig']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'postElts' => ['equivalentDistance', 'spiders'], 
                'widgetsDescription' => [
                    'equivalentDistance' => Widgets::simpleDgrid(Widgets::complete(
                        ['label' => $tr('equivalentDistance'), 'storeArgs' => ['idProperty' => 'idg'],/* 'style' => ['width' => '400px'],*/
                            'colsDescription' => [
                                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                                'sport' => Widgets::description(Widgets::storeSelect([
                                    'edit' => ['storeArgs' => ['data' => Utl::idsNamesStore($this->view->model->options('sport'), $tr)], 'label' => $tr('Sport')]
                                ]), false),
                                'coefficient'  => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $tr('coefficient')], 'storeedit' => ['width' => 150]]), false),
                            ]])),
                    'spiders' =>  Widgets::simpleDgrid(Widgets::complete(
                        ['label' => $tr('spiders'), 'storeArgs' => ['idProperty' => 'idg'],
                            'colsDescription' => [
                                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                                'name' => Widgets::description(Widgets::textBox([
                                    'edit' => ['label' => $tr('Name')]
                                ]), false)
                            ]])),
                    'apply' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('apply'), 'onClickAction' =>
                        "this.pane.form.localActions.programsConfigApplyAction(this.pane);\n"
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
                                'widgets' => ['equivalentDistance', 'spiders'],
                            ],
                            'row2' => [
                                'tableAtts' =>['cols' => 2,  'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 150],
                                'widgets' => ['apply', 'cancel'],
                            ]
                    ]
                ],
                'onOpenAction' => $this->programsConfigOnOpenAction(),
            ],
        ];
    }
    protected function programsConfigOnOpenAction(){
        return <<<EOT
const form = this.form, pane = this;
let valueEquivalentDistance, valueSpiders;
if (form.programsConfig){
     valueEquivalentDistance = form.programsConfig.equivalentDistance ? JSON.parse(form.programsConfig.equivalentDistance) : [];
     valueSpiders = form.programsConfig.spiders ? JSON.parse(form.programsConfig.spiders) : [];
}
pane.markIfChanged = false;
pane.setWidgets({value: {equivalentDistance: valueEquivalentDistance, spiders: valueSpiders}});
pane.markIfChanged = true;
EOT;
    }
}
?>