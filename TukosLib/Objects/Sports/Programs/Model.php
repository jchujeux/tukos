<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Sports\Programs\Questionnaire;
use TukosLib\Google\Calendar;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Google\Sheets;

class Model extends AbstractModel {

    use Questionnaire;
    
	protected $presentationOptions = ['perdate', 'persession'];
    protected $synchnextmondayOptions = ['YES', 'NO'];
    public $defaultSessionsTrackingVersion = 'V1';
    
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'fromdate' => 'VARCHAR(30)  DEFAULT NULL',
            'duration'  => 'VARCHAR(30)  DEFAULT NULL',
            'todate'     => 'VARCHAR(30)  DEFAULT NULL',
        	'googlecalid' => 'VARCHAR(255) DEFAULT NULL',
        	'lastsynctime' => 'timestamp',
        	//'synchrostart' => 'VARCHAR(20) DEFAULT NULL',
        	//'synchroend'   => 'VARCHAR(20) DEFAULT NULL',
        	'synchroweeksbefore'   => 'INT(11) DEFAULT NULL',
        	'synchroweeksafter'   => 'INT(11) DEFAULT NULL',
        	'synchnextmonday' => "ENUM ('" . implode("','", $this->synchnextmondayOptions) . "') DEFAULT NULL",
			'questionnairetime'  =>  'VARCHAR(20)  DEFAULT NULL',
            'weeklies' => 'longtext'
        ];
        parent::__construct($objectName, $translator, 'sptprograms', ['parentid' => ['sptathletes']], ['weeklies'], $colsDefinition, [], [], ['worksheet', 'custom']);
        $this->afterGoogleSync = false;
    }

    function initialize($init=[]){
        return parent::initialize(array_merge(
            ['fromdate' => date('Y-m-d', $nextMondayStamp = strtotime('next monday')), 'duration' =>'[1,"week"]', 'todate' => date('Y-m-d', strtotime('next sunday', $nextMondayStamp)), 
             'loadchart' => $this->defaultLoadChart(), 'performedloadchart' =>  $this->defaultPerformedLoadChart(), 'synchroweeksbefore' => 0, 'synchroweeksafter' => 0, 'synchnextmonday' => 'YES'], $init));
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
        $item['loadchart'] = $this->loadChartData($item);
        $item['performedloadchart'] = $this->performedLoadChartData($item);
        if (!empty($item['parentid'])){
            $peopleModel = Tfk::$registry->get('objectsStore')->objectModel('sptathletes');
            $person = $peopleModel->getOneExtended(['where' => ['id' => $item['parentid']], 'cols' => ['email']]);
            $item['sportsmanemail'] = Utl::getItem('email', $person);
        }
        return $item;
    }
    public function loadChartData($item){
        if (isset($item['id'])){
            $customAtts = $this->getCombinedCustomization(['id' => $item['id']], 'edit', null, ['widgetsDescription', 'loadchart', 'atts']);
            $weekType = empty($customAtts['weektype']) ? 'weekoftheyear' : $customAtts['weektype'];
            $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
            $sessions = $sessionsModel->getAll([
                'where' => $this->user->filter(['parentid' => $item['id'], [['col' => 'mode', 'opr' => '=', 'values' => 'planned'], ['col' => 'mode', 'opr' => 'IS NULL', 'values' => null, 'or' => true],
                    ['col' => 'startdate', 'opr' => '>=', 'values' => $item['fromdate']],['col' => 'startdate', 'opr' => '<=', 'values' => $item['todate']]]], 'sptsessions'),
                'orderBy' => ['startdate' => ' ASC'],  'cols' => ['startdate', 'duration', 'distance', 'elevationgain', 'intensity', 'sport', 'stress']]);
                    $chartData = [];
                    if (!empty($sessions)){
                        $fromDateStamp = empty($item['fromdate']) ? strtotime(reset($sessions)['startdate']) : strtotime($item['fromdate']);
                        $mondayStamp = strtotime(date('w', $fromDateStamp) == 1 ? 'this monday' : 'previous monday', $fromDateStamp);
                        $mondayDate = date('Y-m-d', $mondayStamp);
                        $nextMondayStamp = strtotime('next monday', $mondayStamp);
                        $nextMondayDate = date('Y-m-d', $nextMondayStamp);
                        
                        $toDateStamp = empty($item['todate']) ? strtotime(end($sessions)['startdate']) : strtotime($item['todate']);
                        $lastMondayStamp = strtotime(date('w', $toDateStamp) == 1 ? 'this monday' : 'previous monday', $toDateStamp);
                        $lastMondayDate = date('Y-m-d', $lastMondayStamp);
                        
                        reset($sessions);
                        $session = current($sessions);
                        while(!empty($session) && $session['startdate'] < $mondayDate){
                            $session = next($sessions);
                        }
                        $weekNumber = 0;
                        $normalizationDuration = 600;
                        while($mondayDate <= $lastMondayDate){
                            $weekNumber += 1;
                            //$numberOfSessions = 0;
                            $chartItem = ['id' => $weekNumber, 'week' => $this->tr('W') . ($weekType == 'weekofprogram' ? $weekNumber : date('W', strtotime($mondayDate))), 'weekof' => $mondayDate, 'load' => 0, 'intensity' => 0,
                                'duration' => 0, 'stress' => 0, 'distance' => 0, 'elevationgain' => 0
                            ];
                            while(!empty($session) && $session['startdate'] < $nextMondayDate){
                                if ($session['sport'] != 'rest'){
                                    //$numberOfSessions += 1;
                                    $chartItem['distance'] += floatval($session['distance']);
                                    $chartItem['elevationgain'] += floatval($session['elevationgain']);
                                    $intensity = intval($session['intensity']);//array_search($session['intensity'], Sports::$intensityOptions);
                                    $duration = $session['duration'];
                                    $stress = array_search($session['stress'], Sports::$stressOptions);
                                    $chartItem['intensity'] += $intensity * $duration;
                                    $chartItem['duration'] += $duration;
                                    $chartItem['stress'] += $stress * $duration;
                                }
                                $session = next($sessions);
                            }
                            if ($chartItem['duration'] > 0){
                                $chartItem['load'] = round($chartItem['intensity'] / $normalizationDuration, 2);
                                $chartItem['intensity'] = round($chartItem['intensity'] / $chartItem['duration'], 2);
                                $chartItem['stress'] = round($chartItem['stress'] / $chartItem['duration'], 2);
                                $charItem['duration'] = round($chartItem['duration'], 2);
                            }else{
                                $chartItem['load'] = 0;
                                $chartItem['intensity'] = 0;
                                $chartItem['stress'] = 0;
                            }
                            $chartItem['elevationgain'] = round($chartItem['elevationgain'] / 10, 1);
                            $chartItem['durationTooltip']= $this->tr('duration') . ': ' .Utl::format($chartItem['duration'], 'minutesToHHMM');
                            $chartItem['loadTooltip']= $this->tr('load') . ': ' . $chartItem['load'];
                            $chartItem['intensityTooltip']= $this->tr('intensity') . ': ' . $chartItem['intensity'];
                            $chartItem['stressTooltip']= $this->tr('stress') . ': ' . $chartItem['stress'];
                            $chartItem['distanceTooltip']= $this->tr('distance') . ': ' . $chartItem['distance'] . ' km';
                            $chartItem['elevationgainTooltip']= $this->tr('elevationgain') . ': ' . $chartItem['elevationgain']*10 . ' m';
                            $chartData[] = $chartItem;
                            $mondayDate = $nextMondayDate;
                            $nextMondayStamp = strtotime('next monday', $nextMondayStamp);
                            $nextMondayDate = date('Y-m-d', $nextMondayStamp);
                        }
                        return ['store' => $chartData, 'axes' => ['x' => ['title' => $this->tr($weekType)]]];
                    }
        }
        return $this->defaultLoadChart();
    }
    public function performedLoadChartData($item){
        if (isset($item['id'])){
            $customAtts = $this->getCombinedCustomization(['id' => $item['id']], 'edit', null, ['widgetsDescription', 'performedloadchart', 'atts']);
            $weekType = empty($customAtts['weektype']) ? 'weekoftheyear' : $customAtts['weektype'];
            $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
            $sessions = $sessionsModel->getAll(
                ['where' => 
                    $this->user->filter(['parentid' => $item['id'], 'mode' => 'performed', ['col' => 'startdate', 'opr' => '>=', 'values' => $item['fromdate']],['col' => 'startdate', 'opr' => '<=', 'values' => $item['todate']]],'sptsessions'), 
                'orderBy' => ['startdate' => ' ASC'],  'cols' => ['startdate', 'duration', 'distance', 'elevationgain', 'perceivedeffort', 'sensations', 'mood']]);
            $chartData = [];
            if (!empty($sessions)){
                $fromDateStamp = empty($item['fromdate']) ? strtotime(reset($sessions)['startdate']) : strtotime($item['fromdate']);
                $mondayStamp = strtotime(date('w', $fromDateStamp) == 1 ? 'this monday' : 'previous monday', $fromDateStamp);
                $mondayDate = date('Y-m-d', $mondayStamp);
                $nextMondayStamp = strtotime('next monday', $mondayStamp);
                $nextMondayDate = date('Y-m-d', $nextMondayStamp);
                
                $toDateStamp = empty($item['todate']) ? strtotime(end($sessions)['startdate']) : strtotime($item['todate']);
                $lastMondayStamp = strtotime(date('w', $toDateStamp) == 1 ? 'this monday' : 'previous monday', $toDateStamp);
                $lastMondayDate = date('Y-m-d', $lastMondayStamp);
                
                reset($sessions);
                $session = current($sessions);
                while(!empty($session) && $session['startdate'] < $mondayDate){
                    $session = next($sessions);
                }
                $weekNumber = 0;
                $normalizationDuration = 600;
                while($mondayDate <= $lastMondayDate){
                    $weekNumber += 1;
                    //$numberOfSessions = 0;
                    $chartItem = ['id' => $weekNumber, 'week' => $this->tr('W') . ($weekType == 'weekofprogram' ? $weekNumber : date('W', strtotime($mondayDate))), 'weekof' => $mondayDate, 'distance' => 0, 'elevationgain' => 0,
                        'duration' => 0, 'perceivedeffort' => 0, 'perceivedload' => 0, 'sensations' => 0, 'mood' => 0, 'fatigue' => 0];
                    while(!empty($session) && $session['startdate'] < $nextMondayDate){
                        //$numberOfSessions += 1;
                        $duration = empty($duration = $session['duration']) ? 0 : $duration;
                        $chartItem['distance'] += floatval($session['distance']);
                        $chartItem['elevationgain'] += floatval($session['elevationgain']);
                        $chartItem['duration'] += $duration;
                        $chartItem['perceivedeffort'] += (empty($v = floatval($session['perceivedeffort'])) ? 5 : $v) * $duration;
                        $chartItem['sensations'] += (empty($v = floatval($session['sensations'])) ? 5 : $v) * $duration;
                        $chartItem['mood'] += (empty($v = floatval($session['mood'])) ? 5 : $v) * $duration;
                        $chartItem['fatigue'] += ((empty($v = floatval($session['sensations'])) ? 5 : $v) + (empty($v = floatval($session['mood'])) ? 5 : $v)) / 2 * $duration;
                        $session = next($sessions);
                    }
                    $chartItem['distance'] = round($chartItem['distance'], 1);
                    if ($chartItem['duration'] > 0){
                        $chartItem['perceivedeffort'] = round($chartItem['perceivedeffort'] / $chartItem['duration'], 1);
                        $chartItem['perceivedload'] = round($chartItem['perceivedeffort'] * $chartItem['duration'] / $normalizationDuration, 2);
                        $chartItem['sensations'] = round($chartItem['sensations'] / $chartItem['duration'], 1);
                        $chartItem['mood'] = round($chartItem['mood'] / $chartItem['duration'], 1);
                        $chartItem['fatigue'] = 11 - round($chartItem['fatigue'] / $chartItem['duration'], 1);
                    }
                    $chartItem['elevationgain'] = round($chartItem['elevationgain'] / 10, 1);
                    $chartItem['duration'] = round($chartItem['duration'], 0);
                    $chartItem['durationTooltip']= Utl::format($chartItem['duration'], 'minutesToHHMM');
                    $chartItem['distanceTooltip']= $this->tr('distance') . ': ' . $chartItem['distance'] . ' ' . 'kms';
                    $chartItem['elevationgainTooltip']= $this->tr('elevationgain') . ': ' . $chartItem['elevationgain']*10 . ' ' . 'm';
                    $chartItem['perceivedeffortTooltip'] =$this->tr('perceivedeffort') . ': ' . $chartItem['perceivedeffort'];
                    $chartItem['perceivedloadTooltip'] =$this->tr('perceivedload') . ': ' . $chartItem['perceivedload'];
                    $chartItem['sensationsTooltip'] = $this->tr('sensations') . ': ' . $chartItem['sensations'];
                    $chartItem['moodTooltip'] = $this->tr('mood') . ': ' . $chartItem['mood'];
                    $chartItem['fatigueTooltip'] = $this->tr('fatigue') . ': ' . $chartItem['fatigue'];
                    $chartData[] = $chartItem;
                    $mondayDate = $nextMondayDate;
                    $nextMondayStamp = strtotime('next monday', $nextMondayStamp);
                    $nextMondayDate = date('Y-m-d', $nextMondayStamp);
                }
                return ['store' => $chartData, 'axes' => ['x' => ['title' => $this->tr($weekType)]]];
            }
        }
        return $this->defaultPerformedLoadChart();
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

    public function googleSynchronize($query, $atts = []){
    	$sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
    	$sessionView = Tfk::$registry->get('objectsStore')->objectView('sptsessions');
    	$this->storeSelectCache = [];
        $id = $query['id'];
        $trackingConfiguration = $this->getCombinedCustomization(['id' => $id], 'edit', null, ['widgetsDescription', 'sessionstracking', 'atts', 'dialogDescription', 'paneDescription', 'widgetsDescription']);
        $includeTrackingFormUrl = ($eventFormUrl = Utl::getItem('eventformurl', $trackingConfiguration)) ? $eventFormUrl['atts']['checked'] : false;
        if ($includeTrackingFormUrl){
            $logoFile = ($logo = Utl::getItem('formlogo', $trackingConfiguration)) ? $logo['atts']['value'] : '';
            $formPresentation = ($presentation = Utl::getItem('formpresentation', $trackingConfiguration)) ? $presentation['atts']['value'] : '';
            $formVersion = ($version = Utl::getItem('version', $trackingConfiguration)) ? $version['atts']['value'] : $this->defaultSessionsTrackingVersion;
        }else{
            $logoFile = $formPresentation = $formVersion = '';
        }
        //$logofile = $includeTrackingFormUrl ? (($logo = Utl::getItem('formlogo', $trackingConfiguration)) ? $logo['atts']['value'] : '') : '';
        //$formPresentation = $includeTrackingFormUrl ? (($presentation = Utl::getItem('formpresentation', $trackingConfiguration)) ? $presentation['atts']['value'] : '') : '';
        $where = ['parentid' => $id, [['col' => 'duration', 'opr' => '<>', 'values' => '0'], ['col' => 'mode', 'opr' => '<>', 'values' => 'performed']]];
        if ($minTimeToSync = Utl::getItem('synchrostart', $atts, '', '')){
        	$where[] = ['col' => 'startdate', 'opr' => '>=', 'values' => $minTimeToSync];
        }
        if ($maxTimeToSync = Utl::getItem('synchroend', $atts, '', '')){
        	$where[] = ['col' => 'startdate', 'opr' => '<=', 'values' => $maxTimeToSync];
        }
        $sessionsToSync = $sessionsModel->getAll([
        		'where' => $this->user->filter($where, 'sptsessions'), 'cols' => ['id', 'name', 'startdate', 'sessionid', 'duration', 'intensity', 'sport', 'stress', 'warmup', 'mainactivity', 'warmdown', 'comments', 'googleid']
        ]);
        $calId = empty($atts['googlecalid']) ? $atts['googlecalid'] : $atts['googlecalid'];
        $existingGoogleEvents = [];
        $callback = function($event) use (&$existingGoogleEvents){
            $existingGoogleEvents[($id = $event['id'])] = ['start' => ['date' => $event['start']['date']], 'end' => ['date' => $event['end']['date']], 'summary' => $event['summary'], 'description' => $event['description']];
            if ($colorId = $event['colorId']){
                $existingGoogleEvents[$id]['colorId'] = $colorId;
            }
            return null;
        };
        Calendar::getEventsList([$calId, array_filter(['singleEvents' => true, 'timeMin' => Dutl::toUTC($minTimeToSync), 'timeMax' => Dutl::toUTC(date('Y-m-d', strtotime($maxTimeToSync . ' + 1 day')))])], $callback);
        if (empty($sessionsToSync) && empty($existingGoogleEvents)){
        	Feedback::add($this->tr('nosessiontosync'));
        }else{
	        $updatedEvents = [];
	        $updated = 0;
	        $created = 0;
	        $deleted = 0;
	        if (!empty($sessionsToSync)){
	            foreach($sessionsToSync as $key => $session){
	                $descriptions[$key] = $this->googleDescription($session, $sessionView, $id, $includeTrackingFormUrl, $logoFile, $formPresentation, $formVersion);
	            }
	            $descriptions = json_decode(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode($descriptions)), true);
	            foreach($sessionsToSync as $key => $session){
	                $eventDescription = ['start' => ['date' => $session['startdate']], 'end' => ['date' => $session['startdate']], 'summary' => $session['name'], 'description' => $descriptions[$key]];
	                if (!empty($intensity = $session['intensity'])){
	                    $eventDescription['colorId'] = Calendar::getEventColorId(Sports::$colorNameToHex[Sports::$intensityColorsMap[$intensity]]);
	                }
	                if ((!$googleEventId = Utl::getItem('googleid', $session)) || empty($existingGoogleEvents[$googleEventId])){
	                    $event = Calendar::createEvent($calId, $eventDescription);
	                    $created += 1;
	                    $sessionsModel->updateOne(['id' => $session['id'], 'googleid' => $event->getId()]);
	                }else{
	                    try {
	                        if ($eventDescription != $existingGoogleEvents[$googleEventId]){
	                            Calendar::updateEvent($calId, $googleEventId, $eventDescription);
	                            $updated += 1;
	                        }
	                        $updatedEvents[] = $googleEventId;
	                    } catch (\Exception $e) {
	                        $event = Calendar::createEvent($calId, $eventDescription);
	                        $created +=1;
	                        $sessionsModel->updateOne(['id' => $session['id'], 'googleid' => $event->getId()]);
	                    }
	                }
	            }
	        }
	        if (!empty($existingGoogleEvents)){
	            $eventsToDelete = array_diff(array_keys($existingGoogleEvents), $updatedEvents);
	            foreach ($eventsToDelete as $eventId){
	                Calendar::deleteEvent($calId, $eventId);
	                $deleted +=1;
	            }
	        }
	        Feedback::add($this->tr('synchronizationoutcome') . ' - ' . $this->tr('created') . ': ' .  $created . ' - ' . $this->tr('updated') . ': ' . $updated . ' - ' .$this->tr('deleted') . ': ' . $deleted);
	        $this->updateOne(['id' => $id, 'lastsynctime' => date('Y-m-d H:i:s')]);
        }
        return [];
    }
    public function googleDescription($session, $sessionView, $programId, $includeTrackingFormUrl = false, $logoFile = '', $presentation = '', $version = ''){
        $attCols = ['duration' => 'minutesToHHMM',  'intensity' => 'StoreSelect', 'sport' => 'string', 'stress' => 'string'];
        $contentCols = ['warmup', 'mainactivity', 'warmdown', 'comments'];
        $description = '';
        foreach($attCols as $col => $attType){
            if (!empty($session[$col])){
                $description .= "<b>{$this->tr($col)}" . ($col === 'duration' ? " {$this->tr('estimated')}</b> (HH:MM): " : "</b>: ") . ($attType === 'StoreSelect'
                        ? Utl::format($session[$col], $attType, $this->tr,  $sessionView->dataWidgets[$col]['atts']['edit']['storeArgs']['data'], $this->storeSelectCache)
                        : Utl::format($session[$col], $attType, $this->tr))
                    . '<br>';
            }
        }
        foreach($contentCols as $att){
            if (!empty($session[$att])){
                $description .= '<b>' . $this->tr($att) . '</b>: '. $session[$att] . '<br>';
            }
        }
        if ($includeTrackingFormUrl){
            $sessionName = rawurlencode($session['name']);
            $sport = rawurlencode($session['sport']);
            $description .= '<a href="' .  Tfk::$registry->rootUrl . Tfk::$registry->appUrl .
            "Form/backoffice/Edit?object=sptprograms&form=SessionFeedback&version=$version&parentid=$programId&date={$session['startdate']}&name=$sessionName&sport=$sport&sessionid={$session['sessionid']}" .
            ($logoFile ? "&logo=$logoFile" : '') . ($presentation ? "&presentation=$presentation" : '') . "&targetdb=" . rawurlencode($this->user->encrypt(Tfk::$registry->get('appConfig')->dataSource['dbname'], 'shared')) . '">' .
            $this->tr('SessionFeedback') . '</a><br>';
        }
        return $description;
    }
    public function createCalendar($query, $atts){
    	$newName = $atts['newname'];
    	$newCalendarId = Calendar::createCalendar(['summary' => $newName, 'description' => ''], $atts['newacl'])->getId();
    	Feedback::add($this->tr('new calendar id: ' . $newCalendarId));
		Tfk::addExtra($newCalendarId, ['name' => $newName, 'label' => $newName . ' (' . $newCalendarId . ')']);
    	return ['googlecalid' => $newCalendarId];
    }
	public function updateAcl($query, $atts){
		return ['acl' => (array) Calendar::updateRules($atts)];
	}
    public function deleteCalendar($query, $atts){
    	Calendar::deleteCalendar($atts['googlecalid']);
    	$this->updateOne(['id' => $query['id'], 'googlecalid' => null, 'lastsynctime' => null]);
    	Feedback::add($this->tr('deleted calendar: ' . $atts['googlecalid']));
    	return [];
    }
    public function calendarAcl($query, $atts){
    	return ['acl' => (array) Calendar::getRules([$atts['googlecalid']])];
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
