<?php
namespace TukosLib\Objects\Physio\Cdcs;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Physio\Cdcs\Questionnaire;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Google\Sheets;

class Model extends AbstractModel {

	use Questionnaire;
	
	public $patientCols = ['age', 'weight', 'height', 'imc', 'morphotype', 'sex', 'profession'];
	public $noGridCols = ['avgcmj', 'stdcmj', 'avgsj', 'stdsj', 'avgreactivity', 'stdreactivity', 'avgstiffness', 'stdstiffness', 'countitems', 'countitemslabel'];
	protected $sexOptions = ['male', 'female'];
	protected $morphotypeOptions = ['endomorph', 'mesomorph', 'ectomorph'];
	protected $avgStdQueryTemplate = 
			"SELECT count('*') as countitems, " .
				"AVG(`physiocdcs`.`cmj`) as avgcmj, STDDEV_SAMP(`physiocdcs`.`cmj`) as stdcmj, " .
				"AVG(`physiocdcs`.`sj`) as avgsj, STDDEV_SAMP(`physiocdcs`.`sj`) as stdsj, " .
				"AVG(`physiocdcs`.`reactivity`) as avgreactivity, STDDEV_SAMP(`physiocdcs`.`reactivity`) as stdreactivity, " .
				"AVG(`physiocdcs`.`stiffness`) as avgstiffness, STDDEV_SAMP(`physiocdcs`.`stiffness`) as stdstiffness " .
				"FROM `physiocdcs` " .
				"INNER JOIN `tukos` on `physiocdcs`.`id` = `tukos`.`id` " .
				"INNER JOIN `people` on `tukos`.`parentid`= `people`.`id` " .
			"WHERE `tukos`.`id` > 0 and `people`.`sex`='\${sex}' and `tukos`.`grade` <> 'TEMPLATE' and `physiocdcs`.`cmj` IS NOT NULL";
	protected $computedCols = ['avgcmj', 'avgsj', 'avgreactivity', 'avgstiffness', 'stdcmj', 'stdsj', 'stdreactivity', 'stdstiffness', 'countitems'];
	
	
    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'physiotherapist' => "INT(11) DEFAULT NULL",
            'cdcdate' => 'date NULL DEFAULT NULL',
        	'reason' => 'longtext  DEFAULT NULL',
        	'cmj' => 'float default NULL',
        	'sj' => 'float default NULL',
        	'reactivity' => 'float default NULL',
        	'stiffness' => 'float default NULL',
        	'sport' => 'longtext DEFAULT NULL',
        	'health' => 'longtext DEFAULT NULL',
			'questionnairetime'  =>  'VARCHAR(20)  DEFAULT NULL',
			'clubyesorno'  =>  'VARCHAR(10)  DEFAULT NULL',
        	'clubname' =>  'VARCHAR(255)  DEFAULT NULL',
        	'specialty' =>  'VARCHAR(255)  DEFAULT NULL',
        	'specialtysince' =>  'VARCHAR(255)  DEFAULT NULL',
        	'trainingweek' =>  'VARCHAR(512)  DEFAULT NULL',
        	'sportsgoal' =>  'VARCHAR(255)  DEFAULT NULL',
        	'painstart' =>  'VARCHAR(512)  DEFAULT NULL',
        	'painwhere' =>  'VARCHAR(512)  DEFAULT NULL',
        	'painwhen' =>  'VARCHAR(512)  DEFAULT NULL',
        	'painhow' =>  'VARCHAR(512)  DEFAULT NULL',
        	'painevolution' =>  'VARCHAR(512)  DEFAULT NULL',
        	'paindailyyesorno' =>  'VARCHAR(10)  DEFAULT NULL',
        	'recentchanges' =>  'VARCHAR(255)  DEFAULT NULL',
        	'orthosolesyesorno' =>  'VARCHAR(10)  DEFAULT NULL',
        	'orthosolessince' =>  'VARCHAR(255)  DEFAULT NULL',
        	'orthosoleseaseyesorno' =>  'VARCHAR(10)  DEFAULT NULL',
        	'shoes' =>  'VARCHAR(255)  DEFAULT NULL',
        	'antecedents' =>  'VARCHAR(512)  DEFAULT NULL',
        	'exams' =>  'VARCHAR(512)  DEFAULT NULL',
        	'posture' => 'longtext DEFAULT NULL',
        	'suppleness' => 'longtext DEFAULT NULL',
			'cmjsjreactcomment' => 'VARCHAR(512) DEFAULT NULL',
        	'muscular' => 'longtext DEFAULT NULL',
        	'proprioception' => 'longtext DEFAULT NULL',
        	'runedu' => 'longtext DEFAULT NULL',
        	'ead' => 'longtext DEFAULT NULL',
        	'runpattern' => 'longtext DEFAULT NULL',
        	'photos' => 'longtext DEFAULT NULL',
        	'synthesis' => 'longtext DEFAULT NULL',
        	'bpdefects' => 'longtext DEFAULT NULL',
        	'musculardefects' => 'longtext DEFAULT NULL',
        	'strideanalysis' => 'longtext DEFAULT NULL',
        	'trainingload' => 'longtext DEFAULT NULL',
        	'extrinsic' => 'longtext DEFAULT NULL',
        	'diagnosismk' => 'longtext DEFAULT NULL',
        	'treatmentmk' => 'longtext DEFAULT NULL',
        	'selftreatment' => 'longtext DEFAULT NULL',
        	'recommandationtraining' => 'longtext DEFAULT NULL',
        	'recommandationstretching' => 'longtext DEFAULT NULL',
        	'recommandationstride' => 'longtext DEFAULT NULL',
			'breakfastyesorno' => 'VARCHAR(10) DEFAULT NULL',
			'vegetables' => 'VARCHAR(255) DEFAULT NULL',
			'fruits' => 'VARCHAR(255) DEFAULT NULL',
			'friedfat' => 'VARCHAR(255) DEFAULT NULL',
			'water' => 'VARCHAR(255) DEFAULT NULL',
			'alcool' => 'VARCHAR(255) DEFAULT NULL',
			'snack' => 'VARCHAR(255) DEFAULT NULL',
			'foodrace' => 'VARCHAR(255) DEFAULT NULL',
        ];
        parent::__construct($objectName, $translator, 'physiocdcs', ['parentid' => ['physiopatients'], 'physiotherapist' => ['people']], [], $colsDefinition, [], [], ['custom'], ['name', 'parentid', 'cdcdate']);
    }    
    function initialize($init=[]){
        return parent::initialize(array_merge(['cdcdate' => date('Y-m-d')], $init));
    }
    public function getOneExtended($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $item = parent::getOneExtended($atts, $jsonColsPaths, $jsonNotFoundValue);
    	$item['countitemslabel'] = $this->tr('countitems');
        if (!empty($item['parentid'])){
            $patientsModel = Tfk::$registry->get('objectsStore')->objectModel('physiopatients');
            $item = array_merge($item, $patientsModel->getOneExtended(['where' => ['id' => $item['parentid']], 'cols' => $this->patientCols]));
            if (!empty($item['sex'])){
    			return array_merge($item, SUtl::$store->query(Utl::substitute($this->avgStdQueryTemplate, ['sex' => $item['sex']]))->fetch());
            }else{
            	foreach($this->computedCols as $col){
            		$item[$col] = '';
            	}
            }
        }else{
            foreach ($this->patientCols as $col){
        		$item[$col] = '';
        	}
        }
        return $item;        	
    }
    
    public function getPatientChanged($atts){
        if (!empty($atts['where']['parentid'])){
            $objectsStore = Tfk::$registry->get('objectsStore');
            $patientsModel = $objectsStore->objectModel('physiopatients');
            return $patientsModel->getOneExtended(['where' => ['id' => $atts['where']['parentid']], 'cols' => $this->patientCols]);
        }else{
        	foreach ($this->patientCols as $col){
        		$result[$col] = '';
        	}
        	return $result;
        }
    }
    public function questionnaires($atts){
    	$range = 'Reponses au formulaire 1!A1:AM1007';
    	if (!empty($atts['template'])){
    		$initialValue = $this->duplicateOneExtended($atts['template'], $this->allCols);
    		$initialValue['grade'] = 'NORMAL';
    	}else{
    		$initialValue = $this->initialize();
    	}
    	$values = Sheets::getValues($atts['googlesheetid'], $range);
    	$this->storeNewQuestionnaires($values, $initialValue, 'physiocdcs', 'physiopatients');
    }
}
?>
