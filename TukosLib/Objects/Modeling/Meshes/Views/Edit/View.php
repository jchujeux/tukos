<?php

namespace TukosLib\Objects\Modeling\Meshes\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){
        parent::__construct($actionController);

        $customContents = [
            'row1' => [
                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                'contents' => [
                    'col1' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                        'widgets' => ['id', 'parentid', 'name']
                    ],
                    'col2' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                        'widgets' => ['comments']
                    ]
                ],
            ],
            'rowsource' => [
                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['10%', '50%', '40%']],
                'contents' => [
                    'col1' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                        'widgets' => ['snodes']
                    ],
                    'col2' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                        'widgets' => ['s1dgroups', 's2dgroups', 's3dgroups']
                    ],
                    'col3' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                        'widgets' => ['smeshdiagram']
                    ],
                ]
            ],
            'rowgenerated' => [
                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['10%', '50%', '40%']],
                'contents' => [
                    'col1' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                        'widgets' => ['gnodes']
                    ],
                    'col2' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                        'widgets' => ['g1dgroups', 'g2dgroups', 'g3dgroups', 'gboundaries']
                    ],
                    'col3' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                        'widgets' => ['gmeshdiagram']
                    ],
                ]
            ]
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowbottom', 'rowacl'], $this->dataLayout['contents']));
        $this->actionWidgets['generatemesh'] =  ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('generatemesh'), 'onClickAction' => $this->generateMeshAction()]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'generatemesh';
    }
    function generateMeshAction(){
        return  <<<EOT
const self = this;
require(["tukos/objects/modeling/meshes/LocalActions"], function(LocalActions){
    if (!self.localActions){
        self.localActions = new LocalActions({form: self.form});
    }
    self.localActions.buildMesh();
});
EOT
;
    }
}
?>
