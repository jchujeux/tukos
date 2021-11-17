<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Sports\Programs\Questionnaire;
use TukosLib\Objects\Sports\Programs\GoogleSessionsEvents;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Google\Sheets;

class Model extends AbstractModel {

    use Questionnaire, GoogleSessionsEvents;
    
	protected $presentationOptions = ['perdate', 'persession'];
    protected $synchnextmondayOptions = ['YES', 'NO'];
    public $defaultSessionsTrackingVersion = 'V2';
    
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'coach' => 'MEDIUMINT DEFAULT NULL',
            'sportsmanemail' => 'VARCHAR(50) DEFAULT NULL',
            'coachemail' => 'VARCHAR(50) DEFAULT NULL',
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
            'stsratio' => 'FLOAT DEFAULT NULL',
            'initialsts' => 'FLOAT DEFAULT NULL',
            'initiallts' => 'FLOAT DEFAULT NULL'
        ];
        parent::__construct($objectName, $translator, 'sptprograms', ['parentid' => ['sptathletes'], 'coach' => ['people']], ['weeklies'], $colsDefinition, [], []);
        $this->afterGoogleSync = false;
        $this->setDeleteChildren();
    }

    function initialize($init=[]){
        $coach = $this->user->peopleId();
        $coachEmail = empty($coach) ? '' : Tfk::$registry->get('objectsStore')->objectModel('people')->getOne(['where' => ['id' => $coach], 'cols' => ['email']])['email'];
        return parent::initialize(array_merge(
            ['coach' => $coach, 'coachemail' => $coachEmail, 'fromdate' => $fromDate = date('Y-m-d', $nextMondayStamp = strtotime('next monday')), 'duration' =>'[1,"week"]', 'todate' => date('Y-m-d', strtotime('next sunday', $nextMondayStamp)), 'displayeddate' => $fromDate,
                'loadchart' => $this->defaultLoadChart(), 'performedloadchart' =>  $this->defaultPerformedLoadChart(), 'synchroweeksbefore' => 0, 'synchroweeksafter' => 0, 'synchnextmonday' => 'YES', 'acl'=> ['1' => ['rowId' => 1, 'userid' => Tfk::tukosBackOfficeUserId, 'permission' => '2']]
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
        $item['calendar'] = '';
        return $item;
    }
    public function defaultLoadChart(){
        $paneMode = isset($this->paneMode) ? $this->paneMode : 'Tab';
        $weekType = $this->user->getCustomView($this->objectName, 'edit', $paneMode, ['widgetsDescription', 'loadchart', 'atts', 'weektype']);
        if (empty($weekType)){
            $weekType = 'weekoftheyear';
        }
        return [
            'store' => [['week' => $this->tr('W') . ($weekType == 'weekofprogram' ? 1 : date('W', time())),  'load' => 0, 'intensity' => 0, 'duration' => 0, 'stress' => 0]],
            'axes' => ['x' => ['title' => $this->tr($weekType)]]
        ];
    }
    public function defaultPerformedLoadChart(){
        $paneMode = isset($this->paneMode) ? $this->paneMode : 'Tab';
        $weekType = $this->user->getCustomView($this->objectName, 'edit', $paneMode, ['widgetsDescription', 'performedloadchart', 'atts', 'weektype']);
        if (empty($weekType)){
            $weekType = 'weekoftheyear';
        }
        return [
            'store' => [['week' => $this->tr('W') . ($weekType == 'weekofprogram' ? 1 : date('W', time())),  'distance' => 0, 'elevationgain' => 0, 'duration' => 0, 'perceivedeffort' => 0, 'sensations' => 0, 'mood' => 0, 'fatigue' => 0]],
            'axes' => ['x' => ['title' => $this->tr($weekType)]]
        ];
    }
/*    
    public function updateReport($query, $atts = []){
        $dateFormat = $this->user->dateFormat();
        $program = $this->getOne(['where' => $this->user->filter(['id' => $query['id']], $this->objectName), 'cols' => ['id', 'name', 'fromdate', 'duration', 'todate']]);
        $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');

        $sessionsInPeriod = $sessionsModel->getAll([
            'where' => $this->user->filter(['parentid' => $query['id'], 'startdate' => [ 'BETWEEN', [$atts['firstday'], $atts['lastday']]]], 'sptsessions'),
            'orderBy' => ['startdate' => ' ASC'],
            'cols' => ['id', 'name', 'startdate', 'duration', 'intensity', 'sport', 'stress', 'warmup', 'mainactivity', 'warmdown', 'comments']
        ]);
        
        $optionalCols = ['duration' => 'minutesToHHMM',  'intensity' => 'string', 'sport' => 'string', 'sportimage' => 'inlineImage', 'stress' => 'string'];
        $optionalColsSelected = [];
        foreach ($optionalCols as $col => $format){
            if ($atts[$col] === 'on'){
                $optionalColsSelected[] = $col;
            }
        }
        $numberOfCols = count($optionalColsSelected)+ 2;

        $thAtts = 'style="border: solid;border-collapse: collapse;" ';
        $tdAtts = 'style="border: solid;border-collapse: collapse;" ';
        $rowContent = [['tag' => 'th', 'atts' => $thAtts, 'content' => $this->tr('Session')]];
        foreach ($optionalColsSelected as $col){
            $rowContent[] = ['tag' => 'th', 'atts' => $thAtts, 'content' => $this->tr(ucfirst($col))];
        }
        $rowContent[] = ['tag' => 'th', 'atts' => $thAtts, 'content' => $this->tr('Content')];
        $rows = [['tag' => 'tr', 'content' => $rowContent]];
        $i = 1;
        foreach ($sessionsInPeriod as $session){
            if ($session['sport'] === 'rest'){
                $session['duration'] = '';
            }
            $contentAtts = ['warmup', 'mainactivity', 'warmdown', 'comments'];
            $contentString = '';
            $att = reset($contentAtts);
            while ($att !== false){
                $nextAtt = next($contentAtts);
                if (!empty($session[$att])){
                    $contentString .= $atts['prefix' . $att] .  $session[$att];
                    while ($nextAtt !== false){
                        if (!empty($session[$nextAtt])){
                            $contentString .= $atts['contentseparator' ];
                            break;
                        }else{
                            $nextAtt = next($contentAtts);
                        }
                    }
                }
                $att = $nextAtt;
            }
            $intensity = $session['intensity'];
            $rowContent = [[ 'tag' => 'td',  'atts' => $tdAtts, 'content' => $atts['presentation'] === 'persession' ? 'S' . $i : ucfirst($this->tr(lcfirst(date('l', strtotime($session['startdate'])))))]];
            foreach ($optionalColsSelected as $col){
                $value = ($col === 'sportimage' ? (!empty($session['sport']) ? Tfk::$tukosPhpImages . Sports::$sportImagesMap[$session['sport']] : '') : $session[$col]);
                $rowContent[] = ['tag' => 'td', 'atts' => $tdAtts, 'content' => Utl::format($value, $optionalCols[$col], $this->tr)];
            }
            $rowContent[] = ['tag' => 'td', 'atts' => $tdAtts,  'content' => $contentString];
            $rows[] = ['tag' =>'tr',  'atts' => ($atts['rowintensitycolor'] === 'on'  && !empty($intensity) ? 'style="background-color:' .  Sports::$intensityColorsMap[$intensity]  . ';"' : ''), 'content' => $rowContent];
            $i += 1;
        }
        
        $atts['weeklytable'] = HUtl::buildHtml(
            [['tag' => 'br'],
             ['tag' => 'table',
                'atts' => 'style="text-align:center; border-collapse: collapse;width:100%;"', 
                'content' => [[
                    'tag' => 'tr',
                    'content' => [
                        'tag' => 'td',
                        'atts' => 'colspan=' . $numberOfCols . ' style="background-color: #3a3a3a; color: White; font-size: large; font-weight: bold; border: solid;border-color: Black;"', 
                        'content' => [
                                'tag' => 'table',
                                'content' => [
                                    'tag' => 'tr',
                                    'content' => [[
                                            'tag' => 'td',
                                            'atts' => 'width="10%"',
                                            'content' => Utl::format(Tfk::$tukosPhpImages . 'TDSLogoBlackH64.jpg', 'inlineImage', $this->tr)
                                        ], [
                                            'tag' => 'td',
                                            'atts' => 'style="text-align:center; color: White; font-size: large; font-weight: bold;" width="90%"',
                                            'content' => $atts['presentation'] === 'persession'
                                                ? $program['name'] . '<br>' . $this->tr('week') . ' ' . $atts['weekofprogram'] . ' /  ' . $atts['weeksinprogram']
                                                : $program['name'] . '<br>' . $this->tr('week') . ' ' . $atts['weekoftheyear'] . ': ' . $this->tr('fromdate') . ' ' . date($dateFormat, strtotime($atts['firstday'])) . ' ' .
                                                  $this->tr('todate') . ' ' . date($dateFormat, strtotime($atts['lastday'])),
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ], [
                    'tag' => 'tr',
                    'content' => [
                        'tag' => 'td',
                        'atts' => 'colspan=' . $numberOfCols . ' style="background-color: #99ccff; color: White; font-size: large; font-weight: bold; border: solid;border-color: Black;"', 
                        'content' => '<br>',
                        ],
                    ], 
                    $rows,
                ]
             ],['tag' => 'br'],
            ]
        );
        return ['data' => ['value' => $atts]];
    } 
*/
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
    	$this->storeNewQuestionnaires($values, $initialValue, 'sptprograms', 'sptathletes');
    }
    public function instantiate($class, $params){
        if (empty($this->$class)){
            $fullClassPath = 'TukosLib\\Objects\\Sports\\Programs\\' . $class;
            $this->$class = new $fullClassPath($params);
        }
    }
}
?>
