<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Physio\Cdcs;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Patient', 'Title');

        $customDataWidgets = [//, ['atts' => ['edit' => ['dndParams' => ['accept' => ['dgrid-row']], 'onDropMap' => ['column' => 'comments']]]]

            'name' => ['atts' => ['edit' => ['colspan' => 2, 'style' => ['width' => '24em']]]],
            'parentid' => ['atts' => ['edit' => [
                'onChangeServerAction' => [
                        'inputWidgets' => ['parentid', 'assesmenttype'],
                        'urlArgs' => ['query' => ['params' => json_encode(['getOne' => 'getPatientChanged'])]],
                ]
            ]]],
            'profession' => ViewUtils::textBox($this, 'Profession', ['atts' => ['edit' => ['colspan' => 2, 'disabled' => true, 'style' => ['width' => '24em']]]]),
            'sex'        => ViewUtils::storeSelect('sex', $this, 'Sex', true, ['atts' => ['edit' => ['disabled' => true]]]),
        	'height' => ViewUtils::tukosNumberBox($this, 'Height', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em']]]]),
            'weight' => ViewUtils::tukosNumberBox($this, 'Weight', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em']]]]),
            'morphotype' => ViewUtils::storeSelect('morphotype', $this, 'Morphotype', true, ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '10em']]]]),
            'age' => ViewUtils::textBox($this, 'Age', ['atts' => ['edit' => [ 'style' => ['width' => '5em'], 'disabled' => true]]]),
            'imc' => ViewUtils::tukosNumberBox($this, 'IMC', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.']]]]),
        	'physiotherapist' => ViewUtils::objectSelect($this, 'Physiotherapist', 'people'),
            'cdcdate' => ViewUtils::tukosDateBox($this, 'Assesmentdate'),
        	'reason' => ViewUtils::lazyEditor($this, 'Cdcreason', ['atts' => ['edit' => ['height' => '60px']]]),
        	'sport' => ViewUtils::lazyEditor($this, 'Cdcsport', ['atts' => ['edit' => ['height' => '250px']]]),
        	'health' => ViewUtils::lazyEditor($this, 'Cdchealth', ['atts' => ['edit' => ['height' => '250px']]]),
			'questionnairetime'  =>  ViewUtils::timeStampDataWidget($this, 'QuestionnaireTime', ['atts' => ['edit' => ['disabled' => true]]]),
			'clubyesorno'  => ViewUtils::storeSelect('yesOrNo', $this, 'Clubyesorno', true, ['atts' => ['edit' => ['style' => ['maxWidth' => '10em']]]]),
        	'clubname' => ViewUtils::textBox($this, 'Clubname', ['atts' => ['edit' => ['style' => ['width' => '25em']]]]),
        	'specialty' => ViewUtils::textArea($this, 'Specialty'/*, ['atts' => ['edit' => ['style' => ['width' => '25em']]]]*/),
        	'specialtysince' => ViewUtils::textArea($this, 'SpecialtySince'/*, ['atts' => ['edit' => ['style' => ['width' => '25em']]]]*/),
        	'trainingweek' =>  ViewUtils::textArea($this, 'Trainingweek'/*, ['atts' => ['edit' => ['style' => ['width' => '25em']]]]*/),
        	'sportsgoal' =>  ViewUtils::textArea($this, 'Sportsgoal'/*, ['atts' => ['edit' => ['style' => ['width' => '25em']]]]*/),
        	'painstart' =>  ViewUtils::textArea($this, 'Painstart'),
        	'painwhere' =>  ViewUtils::textArea($this, 'Painwhere'),
        	'painwhen' =>  ViewUtils::textArea($this, 'Painwhen'),
        	'painhow' =>  ViewUtils::textArea($this, 'Painhow'),
        	'painevolution' =>  ViewUtils::textArea($this, 'Painevolution'),
        	'paindailyyesorno' => ViewUtils::storeSelect('yesOrNo', $this, 'Paindailyyesorno', true, ['atts' => ['edit' => ['widgetCellStyle' => ['width' => '6em']]]]),
        	'recentchanges' =>  ViewUtils::textArea($this, 'RecentChanges'),
        	'orthosolesyesorno'  => ViewUtils::storeSelect('yesOrNo', $this, 'Orthosolesyesorno', true, ['atts' => ['edit' => ['widgetCellStyle' => ['width' => '6em']]]]),
        	'orthosolessince' =>  ViewUtils::textArea($this, 'Orthosolessince'),
        	'orthosoleseaseyesorno' => ViewUtils::storeSelect('yesOrNo', $this, 'Orthosoleseaseyesorno', true, ['atts' => ['edit' => ['widgetCellStyle' => ['width' => '6em']]]]),
        	'shoes' =>  ViewUtils::textArea($this, 'Shoes'),
        	'antecedents' =>  ViewUtils::textArea($this, 'Antecedents'),
        	'exams' =>  ViewUtils::textArea($this, 'Exams'),
        	'posture' => ViewUtils::lazyEditor($this, 'Posture', ['atts' => ['edit' => ['height' => '250px']]]),
        	//'clinicshoes' => ViewUtils::lazyEditor($this, 'Cdcclinicshoes', ['atts' => ['edit' => ['height' => '250px']]]),
        	//'flexandtests' => ViewUtils::lazyEditor($this, 'Cdcflexandtests', ['atts' => ['edit' => ['height' => '250px']]]),
        	'suppleness' => ViewUtils::lazyEditor($this, 'Suppleness', ['atts' => ['edit' => ['height' => '250px']]]),
        	'cmjsjreactcomment' => ViewUtils::textArea($this, 'Cmjsjreactcomment'),
        	'muscular' => ViewUtils::lazyEditor($this, 'Cdcmuscular', ['atts' => ['edit' => ['height' => '250px']]]),
        	'proprioception' => ViewUtils::lazyEditor($this, 'Proprioception', ['atts' => ['edit' => ['height' => '250px']]]),
        	'runedu' => ViewUtils::lazyEditor($this, 'Runedu', ['atts' => ['edit' => ['height' => '250px']]]),
        	'ead' => ViewUtils::lazyEditor($this, 'Ead', ['atts' => ['edit' => ['height' => '250px']]]),
	       	'runpattern' => ViewUtils::lazyEditor($this, 'Cdcrunpattern', ['atts' => ['edit' => ['height' => '250px']]]),
        	'photos' => ViewUtils::lazyEditor($this, 'Cdcphotos', ['atts' => ['edit' => ['height' => '250px']]]),
        	'synthesis' => ViewUtils::lazyEditor($this, 'Synthesis', ['atts' => ['edit' => ['height' => '250px']]]),
        	'bpdefects' => ViewUtils::lazyEditor($this, 'Bpdefects', ['atts' => ['edit' => ['height' => '250px']]]),
        	'musculardefects' => ViewUtils::lazyEditor($this, 'Musculardefects', ['atts' => ['edit' => ['height' => '250px']]]),
        	'strideanalysis' => ViewUtils::lazyEditor($this, 'Strideanalysis', ['atts' => ['edit' => ['height' => '250px']]]),
        	'trainingload' => ViewUtils::lazyEditor($this, 'Trainingload', ['atts' => ['edit' => ['height' => '250px']]]),
        	'extrinsic' => ViewUtils::lazyEditor($this, 'Extrinsic', ['atts' => ['edit' => ['height' => '250px']]]),
        		'diagnosismk' => ViewUtils::lazyEditor($this, 'Cdcdiagnosismk', ['atts' => ['edit' => ['height' => '250px']]]),
        	'treatmentmk' => ViewUtils::lazyEditor($this, 'Cdctreatmentmk', ['atts' => ['edit' => ['height' => '250px']]]),
        	'selftreatment' => ViewUtils::lazyEditor($this, 'Cdcselftreatment', ['atts' => ['edit' => ['height' => '250px']]]),
        		'cmj' => ViewUtils::tukosNumberBox($this, 'cmj', ['atts' => ['edit' => ['style' => ['width' => '5em']/*, 'constraints' => ['pattern' => '00.0']*/]]]),
        		'sj' => ViewUtils::tukosNumberBox($this, 'sj', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.0']]]]),
        		'reactivity' => ViewUtils::tukosNumberBox($this, 'reactivity', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.0']]]]),
        		'stiffness' => ViewUtils::tukosNumberBox($this, 'stiffness', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.0']]]]),
        		'avgcmj' => ViewUtils::tukosNumberBox($this, 'avgcmj', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.0']]]]),
        		'avgsj' => ViewUtils::tukosNumberBox($this, 'avgsj', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.0']]]]),
        		'avgreactivity' => ViewUtils::tukosNumberBox($this, 'avgreactivity', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.0']]]]),
        		'avgstiffness' => ViewUtils::tukosNumberBox($this, 'avgstiffness', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.0']]]]),
        		'stdcmj' => ViewUtils::tukosNumberBox($this, 'stdcmj', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.0']]]]),
        		'stdsj' => ViewUtils::tukosNumberBox($this, 'stdsj', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.0']]]]),
        		'stdreactivity' => ViewUtils::tukosNumberBox($this, 'stdreactivity', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.0']]]]),
        		'stdstiffness' => ViewUtils::tukosNumberBox($this, 'stdstiffness', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.0']]]]),
        		'countitemslabel' => ViewUtils::htmlContent($this,''),
        		'countitems' => ViewUtils::textBox($this, 'countitems', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'disabled' => true]]]),
        	'recommandationtraining' => ViewUtils::lazyEditor($this, 'Recommandationtraining', ['atts' => ['edit' => ['height' => '250px']]]),
        	'recommandationstretching' => ViewUtils::lazyEditor($this, 'Recommandationstretching', ['atts' => ['edit' => ['height' => '250px']]]),
        	'recommandationstride' => ViewUtils::lazyEditor($this, 'Recommandationstride', ['atts' => ['edit' => ['height' => '250px']]]),
			'breakfastyesorno' => ViewUtils::storeSelect('yesOrNo', $this, 'Breakfastyesorno', true, ['atts' => ['edit' => ['widgetCellStyle' => ['width' => '6em']]]]),
			'vegetables' => ViewUtils::textArea($this, 'Vegetables'),
			'fruits' => ViewUtils::textArea($this, 'Fruits'),
			'friedfat' => ViewUtils::textArea($this, 'Friedfat'),
			'water' => ViewUtils::textArea($this, 'Water'),
			'alcool' => ViewUtils::textArea($this, 'Alcool'),
			'snack' => ViewUtils::textArea($this, 'Snack'),
			'foodrace' => ViewUtils::textArea($this, 'foodrace'),
        ];
		$subObjects = [
			'physiotemplates' => [
					'atts' => ['title' => $this->tr('synthesistemplates'), 'style' => ['maxHeight' => '1200px'], 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
					'filters' => ['templatetype' => 'cdcssynthesis'],
					'allDescendants' => true,
			],
		];
		$this->customize($customDataWidgets, $subObjects, ['grid' => array_merge($this->model->patientCols, $this->model->noGridCols), 'get' => $this->model->patientCols, 'post' => $this->model->patientCols]);
    }    
}
?>
