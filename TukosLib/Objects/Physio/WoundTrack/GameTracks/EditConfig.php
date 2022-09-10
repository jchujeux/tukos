<?php
namespace TukosLib\Objects\Physio\WoundTrack\GameTracks;

use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;

trait EditConfig {
	
    
    public function setEditConfigActionWidget(){
        $tr = $this->view->tr;
        $this->actionWidgets['editConfig'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('EditConfig'), 'allowSave' => true]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'editConfig';
        $this->actionWidgets['editConfig']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'postElts' => ['trendchartsperrow', 'trendcharts'], 
                'widgetsDescription' => [
                    'trendchartsperrow' => Widgets::numberTextBox(Widgets::complete( ['label' => $tr('Trendchartsperrow'), 'constraints' => ['pattern' =>  "0.######"]])),
                    'trendcharts' =>  Widgets::simpleDgrid(Widgets::complete(
                        ['label' => $tr('Trendcharts'), 'storeArgs' => ['idProperty' => 'idg'],
                         'colsDescription' => [
                                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                                'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('Name')]]), false)
                            ]])),
                    'apply' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('apply'), 'onClickAction' =>
                        "this.pane.form.localActions.editConfigApplyAction(this.pane);\n"
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
                                'widgets' => ['trendchartsperrow', 'trendcharts'],
                            ],
                            'row2' => [
                                'tableAtts' =>['cols' => 2,  'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 150],
                                'widgets' => ['apply', 'cancel'],
                            ]
                    ]
                ],
                'onOpenAction' => $this->editConfigOnOpenAction(),
                'style' => ['minWidth' => '300px']
            ],
        ];
    }
    protected function editConfigOnOpenAction(){
        return <<<EOT
const form = this.form, pane = this, editConfig = form.editConfig;
if (editConfig){
    pane.markIfChanged = false;
    if (editConfig.trendchartsperrow){
        pane.setValueOf('trendchartsperrow', editConfig.trendchartsperrow);
    }
    if (editConfig.trendcharts){
        pane.setValueOf('trendcharts', JSON.parse(editConfig.trendcharts));
    }
    pane.markIfChanged = true;
}
EOT;
    }
}
?>