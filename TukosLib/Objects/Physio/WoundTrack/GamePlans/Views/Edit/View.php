<?php
namespace TukosLib\Objects\Physio\WoundTrack\GamePlans\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Objects\Views\LocalActions;
use TukosLib\Objects\Physio\WoundTrack\IndicatorsConfig;
use TukosLib\Utils\Utilities as Utl;

class View extends EditView{

	use LocalActions, IndicatorsConfig;
	
	function __construct($actionController){
       parent::__construct($actionController);

        $customContents = [
            'row1' => [
                'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 80],
                'widgets' => ['id', 'parentid', 'physiotherapist', 'organization', 'name', 'dateupdated']
            ],
            'row2' => [
                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'widgetWidths' => ['60%', '40%']],
                'contents' => [
                    'col1' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                        'widgets' => ['diagnostic']
                        ],
                    'col2' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                        'contents' => [
                            'row1' => [
                                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                                'contents' => [
                                    'col1' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                                        'widgets' => ['pathologyof']
                                    ],
                                    'col2' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                                        'widgets' => ['pathologyoftriangle']
                                    ],
                                ],
                            ],
                            'row2' => [
                                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                                'contents' => [
                                    'col1' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                                        'widgets' => ['woundstartdate']
                                    ],
                                    'col2' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                                        'widgets' => ['wounddatedifference']
                                    ],
                                ],
                            ],
                            'row3' => [
                                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                                'contents' => [
                                    'col1' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                                        'widgets' => ['treatmentstartdate']
                                    ],
                                    'col2' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                                        'widgets' => ['treatmentdatedifference']
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ],
            'row3' => [
                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetCellStyle' => ['verticalAlign' => 'top']],      
                'widgets' => [ 'training', 'pain', 'exercises', 'biomechanics'],
            ],
            'rowcomments' => [
                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
                'widgets' => ['comments'],
            ],
            'rowindicators' => [
                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetCellStyle' => ['textAlign' => 'center', 'width' => '100%%']],
                'widgets' => [],
            ],
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowbottom', 'rowhistory', 'rowacl'], $this->dataLayout['contents']));
        $this->setIndicatorsConfigActionWidget();
        $this->onOpenAction =  $this->openAction();
	}
	public function openAction(){
	    $currentDate = date('Y-m-d');
	    return <<<EOT
var form = this;
this.markIfChanged = true;
this.setValueOf('dateupdated', '$currentDate');
require (["tukos/objects/physio/woundTrack/gameplans/LocalActions"], function(LocalActions){
    form.localActions = new LocalActions({form: form});
});
EOT
	    ;
	}
}
?>
