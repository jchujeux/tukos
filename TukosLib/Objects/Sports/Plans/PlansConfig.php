<?php
namespace TukosLib\Objects\Sports\Plans;

use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;

trait PlansConfig {
	
    
    public function setPlansConfigActionWidget(){
        $tr = $this->view->tr;
        $this->actionWidgets['plansconfig'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('PlansConfig'), 'allowSave' => true]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'plansconfig';
        $this->actionWidgets['plansconfig']['atts']['dialogDescription'] = [
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
                        "this.pane.form.localActions.plansConfigApplyAction(this.pane);\n"
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
                'onOpenAction' => $this->plansConfigOnOpenAction(),
            ],
        ];
    }
    protected function plansConfigOnOpenAction(){
        return <<<EOT
const form = this.form, pane = this;
let valueEquivalentDistance, valueSpiders;
if (form.plansConfig){
     valueEquivalentDistance = form.plansConfig.equivalentDistance ? JSON.parse(form.plansConfig.equivalentDistance) : [];
     valueSpiders = form.plansConfig.spiders ? JSON.parse(form.plansConfig.spiders) : [];
}
pane.markIfChanged = false;
pane.setWidgets({value: {equivalentDistance: valueEquivalentDistance, spiders: valueSpiders}});
pane.markIfChanged = true;
EOT;
    }
}
?>