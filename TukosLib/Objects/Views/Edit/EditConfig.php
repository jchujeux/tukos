<?php
namespace TukosLib\Objects\Views\Edit;

use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;

trait EditConfig {
    
    
    public function setEditConfigActionWidget(){
        $tr = $this->view->tr;
        $this->actionWidgets['editConfig'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('EditConfig'), 'allowSave' => true]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'editConfig';
        $this->actionWidgets['editConfig']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'postElts' => ['chartsperrow', 'charts'],
                'widgetsDescription' => [
                    'chartsperrow' => Widgets::numberTextBox(Widgets::complete( ['label' => $tr('Chartsperrow'), 'constraints' => ['pattern' =>  "0.######"]])),
                    'charts' =>  Widgets::simpleDgrid(Widgets::complete(
                        ['label' => $tr('Tabcharts'), 'storeArgs' => ['idProperty' => 'idg'], 'style' => ['width' => '700px'],'sort' => [['property' => 'rowId', 'descending' => false]],
                            'colsDescription' => [
                                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                                'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('Name')], 'storeedit' => ['width' => 300]]), false),
                                'chartType' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['trend', 'spider', 'pie', 'repartition', 'xy'], $tr)], 'label' => $tr('Charttype')], 'storeedit' => ['width' => 200]]), false),
                                'colspan' => Widgets::numberTextBox(Widgets::complete( ['edit' => ['label' => $tr('Colspan'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 100]]), false),
                            ]])),
                    'apply' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('apply'), 'onClickAction' => $this->editConfigApplyAction()
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
                            'widgets' => ['chartsperrow', 'charts'],
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
if (editConfig  && !this.isInitialized){
    pane.markIfChanged = false;
    if (editConfig.chartsperrow){
        pane.setValueOf('chartsperrow', editConfig.chartsperrow);
    }
    if (editConfig.charts){
        pane.setValueOf('charts', utils.toNumeric(editConfig.charts, 'id'));
    }
    pane.markIfChanged = true;
    this.isInitialized = true;
}
EOT;
    }
    protected function editConfigApplyAction(){
        return <<<EOT
const pane = this.form, form = pane.form, changedValues = pane.changedValues();
if (!utils.empty(changedValues)){
	form.editConfig = form.editConfig || {};
	if (changedValues.chartsperrow){
		lang.setObject('customization.editConfig.chartsperrow', form.editConfig.chartsperrow, form);
	}
	if (changedValues.charts){
		const newChartsValue = utils.toObject(lang.clone(pane.getWidget('charts').get('value')), 'id', false, true);
        lang.setObject('customization.editConfig.charts', newChartsValue, form);
        utils.forEach(newChartsValue, function(value, id){
            if (JSON.stringify(value) === '{"~delete":true}'){
                lang.setObject('customization.widgetsDescription.' + 'chart' + id, value, form);
            }
        });
	}
	Pmg.setFeedback(Pmg.message('savecustomtoupdatecharts'), null, null, true);
}
EOT;
    }
}
?>