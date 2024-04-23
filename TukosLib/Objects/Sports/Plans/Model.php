<?php
namespace TukosLib\Objects\Sports\Plans;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Sports\Plans\Questionnaire;
use TukosLib\Objects\Sports\Plans\GoogleWorkoutsEvents;
use TukosLib\Objects\Sports\Strava\AuthorizeAndSynchronize;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Google\Sheets;

class Model extends AbstractModel {

    use Questionnaire, GoogleWorkoutsEvents, AuthorizeAndSynchronize;
    
	protected $presentationOptions = ['perdate', 'perworkout'];
    protected $synchnextmondayOptions = ['YES', 'NO'];
    public $defaultWorkoutsTrackingVersion = 'V2';
    
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'coachid' => 'MEDIUMINT DEFAULT NULL',
            'sportsmanemail' => 'VARCHAR(50) DEFAULT NULL',
            'coachemail' => 'VARCHAR(50) DEFAULT NULL',
            'coachorganization' => 'VARCHAR(50) DEFAULT NULL',
            'fromdate' => 'VARCHAR(30)  DEFAULT NULL',
            'duration'  => 'VARCHAR(30)  DEFAULT NULL',
            'todate'     => 'VARCHAR(30)  DEFAULT NULL',
        	'googlecalid' => 'VARCHAR(255) DEFAULT NULL',
        	'lastsynctime' => 'timestamp',
        	'synchroweeksbefore'   => 'INT(11) DEFAULT NULL',
        	'synchroweeksafter'   => 'INT(11) DEFAULT NULL',
        	'synchnextmonday' => "ENUM ('" . implode("','", $this->synchnextmondayOptions) . "') DEFAULT NULL",
			'questionnairetime'  =>  'VARCHAR(20)  DEFAULT NULL',
            'weeklies' => 'longtext',
            'stsdays' => 'INT DEFAULT NULL',
            'ltsdays' => 'INT DEFAULT NULL',
            'initialsts' => 'FLOAT DEFAULT NULL',
            'initiallts' => 'FLOAT DEFAULT NULL',
            'initialhracwr' => 'FLOAT DEFAULT NULL',
            'displayfrom' => 'longtext'
        ];
        parent::__construct($objectName, $translator, 'sptplans', ['parentid' => ['sptathletes'], 'coachid' => ['people']], ['weeklies', 'displayfrom'], $colsDefinition, [], []);
        $this->afterGoogleSync = false;
        $this->setDeleteChildren();
    }

    function initialize($init=[]){
        $coach = $this->user->peopleId();
        $coachEmail = empty($coach) ? '' : Tfk::$registry->get('objectsStore')->objectModel('people')->getOne(['where' => ['id' => $coach], 'cols' => ['email']])['email'];
        $today = date('Y-m-d', $nextMondayStamp = strtotime('next monday'));
        return parent::initialize(array_merge(
            ['coachid' => $coach, 'coachemail' => $coachEmail, 'fromdate' => $fromDate = $today, 'displayfromdate' => $today, 'duration' =>'[1,"week"]', 'todate' => date('Y-m-d', strtotime('next sunday', $nextMondayStamp)), 'displayeddate' => $fromDate,
                'synchroweeksbefore' => 0, 'synchroweeksafter' => 0, 'synchnextmonday' => 'YES', 
                'stsdays' => 7, 'ltsdays' => 42, 'initiallts' => 30, 'initialprogressivity' => 1, 'initialsts' => 30, 'acl'=> ['1' => ['rowId' => 1, 'userid' => Tfk::tukosBackOfficeUserId, 'permission' => '2']]
            ], $init));
    }
    
    public function getOneExtended($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
    	if (in_array('synchrostart', $atts['cols']) && !in_array('synchroweeksbefore', $atts['cols'])){
        	array_push($atts['cols'], 'synchroweeksbefore');
        }
        if (in_array('synchroend', $atts['cols']) && !in_array('synchroweeksafter', $atts['cols'])){
        	array_push($atts['cols'], 'synchroweeksafter');
        }
        $item = parent::getOneExtended($atts, $jsonColsPaths, $jsonNotFoundValue);
        if (empty($item)){
            Feedback::add($this->tr('programnotfound'));
            throw new \Exception($this->tr('programnotfound') . ' - ' . Utl::getItem('id', $atts['where']));
        }
        $item['calendar'] = '';
        if (!empty($displayfrom = Utl::extractItem('displayfrom', $item))){
            $item = array_merge($item, $displayfrom);
        }
        return $item;
    }
    public function updateOneExtended($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
        $this->processLargeCols($newValues);
        if (!$jsonFilter && (!empty($displayfromCols = array_diff(array_keys($newValues), $this->allCols)))){
            $newValues['displayfrom'] = array_intersect_key(Utl::extractItems($displayfromCols, $newValues), ['displayfromdate' => true, 'displayfromsts' => true, 'displayfromlts' => true]);
        }
        return $this->updateOne($newValues, $atts, $insertIfNoOld, true, $init);
    }
    public function insertExtended($values, $init=false, $jsonFilter = false){
        if (!$jsonFilter && (!empty($displayfromCols = array_diff(array_keys($values), $this->allCols)))){
            $values['displayfrom'] = Utl::extractItems($displayfromCols, $values);
        }
        return parent::insertExtended($values, $init, $jsonFilter);
    }
    public function insert($values, $init = false, $jsonFilter = false, $reference = null){
    	if(!empty($values['fromdate']) && !empty($values['todate']) && empty($values['duration'])){
    	    $values['duration'] = DUtl::duration(strtotime($values['todate']) - strtotime($values['fromdate']), ['week']);
    	}
    	return parent::insert($values, $init, $jsonFilter, $reference);
    }
    public function questionnaires($atts){
    	$range = 'Reponses au formulaire 1!A1:AM1007';
    	if (!empty($atts['template'])){
    		$initialValue = $this->duplicateOneExtended($atts['template'], $this->allCols);
    		$initialValue['grade'] = 'NORMAL';
    	}else{
    		$initialValue = $this->initialize();
    	}
    	unset($initialValue['duration']);
    	$values = Sheets::getValues($atts['googlesheetid'], $range);
    	$this->storeNewQuestionnaires($values, $initialValue, 'sptplans', 'sptathletes');
    }
    public function instantiate($class, $params){
        if (empty($this->$class)){
            $fullClassPath = 'TukosLib\\Objects\\Sports\\Plans\\' . $class;
            $this->$class = new $fullClassPath($params);
        }
    }
    public function stravaCols(){
        return ['name', 'stravaid', 'startdate', 'starttime', 'sport', 'duration', 'timemoving', 'distance', 'elevationgain', 'avghr', 'avgpw', 'avgcadence'];
    }
    public function activityKpis(){
        return ['heartrate_avgload', 'heartrate_load', 'heartrate_timeabove_threshold_90', 'heartrate_timeabove_threshold', 'heartrate_timeabove_threshold_110', 'power_avgload', 'power_load'];
    }
    public function itemsModel(){
        return Tfk::$registry->get('objectsStore')->objectModel('sptworkouts');
    }
}
?>
