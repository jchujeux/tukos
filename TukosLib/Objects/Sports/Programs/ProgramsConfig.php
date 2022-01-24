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
                'widgetsDescription' => [
                    'equivalentdistance' => Widgets::simpleDgrid(Widgets::complete(
                        ['label' => $tr('equivalentdistance'), 'storeArgs' => ['idProperty' => 'idg'],/* 'style' => ['width' => '400px'],*/
                            'colsDescription' => [
                                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                                'sport' => Widgets::description(Widgets::storeSelect([
                                    'edit' => ['storeArgs' => ['data' => Utl::idsNamesStore($this->view->model->options('sport'), $tr)], 'label' => $tr('Sport')]
                                ]), false),
                                'coefficient'  => Widgets::description(Widgets::NumberTextBox(['edit' => ['label' => $tr('coefficient')], 'storeedit' => ['width' => 150]]), false),
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
                                'widgets' => ['equivalentdistance'],
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
var form = this.form, pane = this, value;
if (form.programsConfig && form.programsConfig.equivalentDistance){
    value = JSON.parse(form.programsConfig.equivalentDistance);
}else{
    value = [{rowId: 1, id: 1, sport: 'bicycle', coefficient: 1.0}, {rowId: 2, id: 2, sport: 'swimming', coefficient: 1.0}];
}
pane.setWidgets({value: {equivalentdistance: value}});
EOT;
    }
}
?>