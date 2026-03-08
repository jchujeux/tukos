<?php

namespace TukosLib\Objects\Modeling\Simulations\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController);

        $customContents = [
            'row1' => [
                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'widgetWidths' => ['40%', '25%', '35%']],
                'contents' => [
                    'col1' => [
                        'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                        'widgets' => ['id', 'parentid', 'name', 'dimension', 'ndof', 'linearity', 'nonlinearoptions', 'timedependency', 'meshid', 'properties']
                    ],
                    'col3' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert',  'showLabels' => true],
                        'widgets' => ['dofnames']
                    ],
                    'col2' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert', 'showLabels' => true],
                        'widgets' => ['comments']
                    ]
                ],
            ],
            'rowsource' => [
                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'widgetWidths' => ['20%', '20%', '60%']],
                'contents' => [
                    'col1' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert',  'showLabels' => true],
                        'widgets' => ['boundariesconstraints', 'nodalconstraints']
                    ],
                    'col2' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert',  'showLabels' => true],
                        'widgets' => ['boundariesrhs', 'nodalrhs']
                    ],
                    'col3' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert',  'showLabels' => true],
                        'widgets' => ['groups']
                    ],
                ],
            ],
           'rowDiagrams' => [
                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['30%', '70%']],
                'widgets' => ['nodalsolution', 'gmeshdiagram']
            ]
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowbottom', 'rowacl'], $this->dataLayout['contents']));
        $this->actionWidgets['runsimulation'] =  ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('runsimulation'), 'onClickAction' => $this->runSimulation('run')]];
        $this->actionWidgets['updateDiagram'] =  ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('updateSolutionDiagram'), 'onClickAction' => $this->runSimulation('updateDiagram')]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'runsimulation';
        $this->actionLayout['contents']['actions']['widgets'][] = 'updateDiagram';
        $this->onOpenAction = $this->openAction();
    }
    function openAction(){
        return  <<<EOT
const self = this;
require(["tukos/objects/modeling/simulations/LocalActions"], function(LocalActions){
    if (!self.localActions){
        self.localActions = new LocalActions({form: self});
    }
    self.localActions.setNodalDofColumns();
});
EOT
        ;
    }
function runSimulation($mode){
        return  <<<EOT
this.form.localActions.runSimulation('$mode');
EOT
        ;
    }
}
?>
