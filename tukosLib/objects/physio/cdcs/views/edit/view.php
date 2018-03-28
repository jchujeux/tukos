<?php

namespace TukosLib\Objects\Physio\Cdcs\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

	function __construct($actionController){
		parent::__construct($actionController);
		$this->dataLayout   = [
			'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false,  'content' => '', 'orientation' => 'vert'],
			'contents' => [
				'row1' => [
					'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'widgetWidths' => ['50%', '50%']],
					'contents' => [
						'col1' => [
							'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => true],
							'widgets' => ['id', 'name', 'questionnairetime', 'physiotherapist', 'cdcdate', 'parentid', 'sex', 'profession', 'age', 'weight', 'height', 'imc', 'morphotype']
						],
						'col2' => [
							'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
							'widgets' => ['reason'],
						]
					],
				],
				'row2' => [
					'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
					'contents' => [
						'row1a' => [
							'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'label' => $this->view->tr('Sportcontext')],
							'contents' => [
									'row1' => [
										'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'label' => $this->view->tr('Clubbelonging')],
										'widgets' => ['clubyesorno', 'clubname']
									],
									'row2' => [
											'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
											'widgets' => ['specialty', 'specialtysince', 'trainingweek', 'sportsgoal'],
									]
							]
						],
						'row1b' => [
							'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'label' => $this->view->tr('Health')],
							'contents' => [
								'col1' => [
									'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'label' => $this->view->tr('Pain')],
									'contents' => [
										'row1' => [
											'tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'labelWidth' => '-1'],
											'widgets' => ['painstart','painwhere', 'painwhen', 'painhow', 'painevolution', 'recentchanges', 'paindailyyesorno'],
										],
									]
								],
								'col2' => [
									'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'label' => $this->view->tr('Solesandshoes'), 'widgetWidths' => ['15%', '85%']],
									'contents' => [
										'col1' => [
											'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 30],
											'widgets' => ['orthosolesyesorno', 'orthosoleseaseyesorno'],
										],
										'col2' => [
											'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
											'widgets' => ['orthosolessince', 'shoes'],
										]
									]
								],
								'col3' => [
									'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'label' => $this->view->tr('Antecedentsandexams')],
									'widgets' => ['antecedents', 'exams'],
								]
							]
						],
						'row1c' => [
							'tableAtts' => ['cols' => 8, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'label' => $this->view->tr('Diet'), 'widgetWidths' => ['50%', '50%']],
							'widgets' => ['breakfastyesorno', 'vegetables', 'fruits', 'friedfat', 'water', 'alcool', 'snack', 'foodrace'],
						],
						'row2' => [
							'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'label' => $this->view->tr('Clinicalevaluation'), 'widgetWidths' => ['50%', '50%']],
							'widgets' => ['posture', 'suppleness', 'proprioception'],
						],
						'row2b' => [
							'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'label' => $this->view->tr('Cdcmuscular'), 'widgetWidths' => ['50%', '50%']],
							'contents' => [
								'col1'=> [
									'tableAtts' =>	['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
									'contents' => [
										'row1' => [
											'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 150],
											'widgets' => ['cmj', 'avgcmj', 'stdcmj','sj', 'avgsj', 'stdsj', 'reactivity', 'avgreactivity', 'stdreactivity', 'stiffness', 'avgstiffness', 'stdstiffness'],
										],
										'row2' => [
											'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'widgetCellStyle' => ['textAlign' => 'right']],
												'widgets' => ['countitemslabel', 'countitems']
										],
										'row3' => [
											'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
											'widgets' => ['cmjsjreactcomment']
										],
									]
								],
								'col2' => [
										'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert'],
										'widgets' => ['muscular'],
								
								]
							]
						],
						'row3' => [
							'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'label' => $this->view->tr('deferredanalysis'), 'widgetWidths' => ['50%', '50%']],
							'widgets' => ['runedu', 'ead', 'runpattern','photos'],
						],
						'row4' => [
							'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'label' => $this->view->tr('synthesis'), 'widgetWidths' => ['70%', '30%']],
							'contents' => [
								'col1' => [
									'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'widgetWidths' => ['50%', '50%']],
									'widgets' => ['bpdefects','musculardefects','strideanalysis','trainingload', 'extrinsic', 'diagnosismk','treatmentmk','selftreatment', 'recommandationtraining', 'recommandationstretching', 'recommandationstride'],
								],
									'col2' => [
									'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
									'widgets' => ['physiotemplates'],
								]
							]
						],
					],
				],
				'row3' => [
					 'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true],
					 'widgets' => ['comments']
				],
				'row4' => [
					 'tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 60],
					 'widgets' => ['permission', 'grade', 'contextid', 'updated', 'updator', 'created', 'creator']
				],
			]
		];
		
		if (isset($this->view->dataWidgets['configstatus'])){
			$this->dataLayout['contents']['row4']['widgets'][] = 'configstatus';
		}
	}
}
?>
