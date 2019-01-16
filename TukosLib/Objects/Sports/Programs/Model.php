<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\Sports\AbstractModel;
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
    
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'fromdate' => 'VARCHAR(30)  DEFAULT NULL',
            'duration'  => 'VARCHAR(30)  DEFAULT NULL',
            'todate'     => 'VARCHAR(30)  DEFAULT NULL',
        	'googlecalid' => 'VARCHAR(255) DEFAULT NULL',
        	'lastsynctime' => 'timestamp',
        	'synchrostart' => 'VARCHAR(20) DEFAULT NULL',
        	'synchroend'   => 'VARCHAR(20) DEFAULT NULL',
        	'synchroweeksbefore'   => 'INT(11) DEFAULT NULL',
        	'synchroweeksafter'   => 'INT(11) DEFAULT NULL',
        	'synchnextmonday' => "ENUM ('" . implode("','", $this->synchnextmondayOptions) . "') DEFAULT NULL",
			'questionnairetime'  =>  'VARCHAR(20)  DEFAULT NULL',
        ];
        parent::__construct(
            $objectName, $translator, 'sptprograms',
            ['parentid' => ['sptathletes']],
            [], $colsDefinition, '', [], ['worksheet', 'custom']
        );
        $this->afterGoogleSync = false;
    }

    function initialize($init=[]){
        return parent::initialize(array_merge(['duration' =>'[1,"week"]', 'loadchart' => $this->defaultLoadChart(), 'synchroweeksbefore' => 0, 'synchroweeksafter' => 0, 'synchnextmonday' => 'YES'], $init));
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
        if (!empty($item['parentid'])){
            $peopleModel = Tfk::$registry->get('objectsStore')->objectModel('sptathletes');
            $person = $peopleModel->getOneExtended(['where' => ['id' => $item['parentid']], 'cols' => ['email']]);
            $item['sportsmanemail'] = $person['email'];
        }
        if (!is_null($weeksBefore = Utl::getItem('synchroweeksbefore', $item))){
    		$item['synchrostart'] = date('Y-m-d', strtotime('last monday') - $weeksBefore * 7 * 24 * 3600);
    	}
    	if (!is_null($weeksAfter = Utl::getItem('synchroweeksafter', $item))){
    		$item['synchroend'] = date('Y-m-d', strtotime('next sunday') + $weeksAfter *  (7 * 24) * 3600);
        }
        return $item;
    }
    
    public function loadChartData($item){
        if (isset($item['id'])){
            $paneMode = isset($this->paneMode) ? $this->paneMode : 'Tab';
        	$customAtts = $this->getCombinedCustomization(['id' => $item['id']], 'edit', strtolower($paneMode), ['widgetsDescription', 'loadchart', 'atts']);
            $weekType = empty($customAtts['weektype']) ? 'weekoftheyear' : $customAtts['weektype'];
            $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
            $sessions = $sessionsModel->getAll(['where' => $this->user->filter(['parentid' => $item['id']], 'sptsessions'), 'orderBy' => ['startdate' => ' ASC'],  'cols' => ['startdate', 'duration', 'intensity', 'sport', 'stress']]);
    
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
                $normalizationVolume = 10;
                while($mondayDate <= $lastMondayDate){
                    $weekNumber += 1;
                    $numberOfSessions = 0;
                    $chartItem = ['week' => $this->tr('Week')[0] . ($weekType == 'weekofprogram' ? $weekNumber : date('W', strtotime($mondayDate))), 'weekof' => $mondayDate, 'load' => 0, 'intensity' => 0, 'volume' => 0, 'stress' => 0];
                    while(!empty($session) && $session['startdate'] < $nextMondayDate){
                        if ($session['sport'] != 'rest'){ 
                            $numberOfSessions += 1;
                            $intensity = array_search($session['intensity'], Sports::$intensityOptions);
                            $volume = DUtl::seconds($session['duration']) / 3600;
                            $stress = array_search($session['stress'], Sports::$stressOptions);
                            $chartItem['intensity'] += $intensity * $volume;
                            $chartItem['volume'] += $volume;
                            $chartItem['stress'] += $stress * $volume;
                        }
                        $session = next($sessions);
                    }
                    if ($chartItem['volume'] > 0){
                    	$chartItem['load'] = round($chartItem['intensity'] / $normalizationVolume, 2);
                        $chartItem['intensity'] = round($chartItem['intensity'] / $chartItem['volume'], 2);
                        $chartItem['stress'] = round($chartItem['stress'] / $chartItem['volume'], 2);
                        $charItem['volume'] = round($chartItem['volume'], 2);
                    }else{
                        $chartItem['load'] = 0;
                        $chartItem['intensity'] = 0;
                        $chartItem['stress'] = 0;
                    }
                    $chartItem['volumeTooltip']= $chartItem['volume'] . ' ' . $this->tr('hour') . '(s)';
                    $chartItem['loadTooltip']= $chartItem['load'];
                    $chartItem['intensityTooltip']= $chartItem['intensity'];
                    $chartItem['stressTooltip']= $chartItem['stress'];
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
     
     public function defaultLoadChart(){
    	$paneMode = isset($this->paneMode) ? $this->paneMode : 'Tab';
     	$weekType = $this->user->getCustomView($this->objectName, 'edit', $paneMode, ['widgetsDescription', 'loadchart', 'atts', 'weektype']);
        if (empty($weekType)){
            $weekType = 'weekoftheyear';
        }
        return [
            'store' => [['week' => $this->tr('Week')[0] . ($weekType == 'weekofprogram' ? 1 : date('W', time())),  'load' => 0, 'intensity' => 0, 'volume' => 0, 'stress' => 0]],
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
        
        $optionalCols = ['duration' => 'numberUnit',  'intensity' => 'string', 'sport' => 'string', 'sportimage' => 'inlineImage', 'stress' => 'string'];
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
                $value = ($col === 'sportimage' ? (!empty($session['sport']) ? Tfk::tukosSite . 'tukos/images/' . Sports::$sportImagesMap[$session['sport']] : '') : $session[$col]);
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
                                            'content' => Utl::format(Tfk::tukosSite . 'tukos/images/TDSLogoBlackH64.jpg', 'inlineImage', $this->tr)
                                        ], [
                                            'tag' => 'td',
                                            'atts' => 'style="text-align:center; color: White; font-size: large; font-weight: bold;" width="90%"',
                                            'content' => $atts['presentation'] === 'persession'
                                                ? $program['name'] . '<br>' . $this->tr('week') . ' ' . $atts['weekofprogram'] . ' /  ' . $atts['weeksinprogram']
                                                : $program['name'] . '<br>' . $this->tr('week') . $atts['weekoftheyear'] . ': ' . $this->tr('fromdate') . ' ' . date($dateFormat, strtotime($atts['firstday'])) . ' ' . $this->tr('todate') . ' ' . date($dateFormat, strtotime($atts['lastday'])),
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
        $id = $query['id'];
        $program = $this->getOne(['where' => ['id' => $id], 'cols' => ['synchrostart', 'synchroend', 'googlecalid', 'lastsynctime']]);
        $where = ['parentid' => $id];
        if ($minTimeToSync = Utl::getItem('synchrostart', $program, '', '')){
        	$where[] = ['col' => 'startdate', 'opr' => '>=', 'values' => $minTimeToSync];
        }
        if ($maxTimeToSync = Utl::getItem('synchroend', $program, '', '')){
        	$where[] = ['col' => 'startdate', 'opr' => '<=', 'values' => $maxTimeToSync];
        }
        $sessionsToSync = $sessionsModel->getAll([
        		'where' => $this->user->filter($where, 'sptsessions'), 'cols' => ['id', 'name', 'startdate', 'duration', 'intensity', 'sport', 'stress', 'warmup', 'mainactivity', 'warmdown', 'comments', 'googleid']
        ]);
        if (empty($sessionsToSync)){
        	Feedback::add($this->tr('nosessiontosync'));
        }else{
	        $calId = empty($atts['googlecalid']) ? $program['googlecalid'] : $atts['googlecalid'];
	        $existingGoogleEvents = [];
	        $callback = function($event) use (&$existingGoogleEvents){
	        	$existingGoogleEvents[($id = $event['id'])] = ['start' => ['date' => $event['start']['date']], 'end' => ['date' => $event['end']['date']], 'summary' => $event['summary'], 'description' => $event['description']];
	        	if ($colorId = $event['colorId']){
	        		$existingGoogleEvents[$id]['colorId'] = $colorId;
	        	}
	        	return null;
	        };
			Calendar::getEventsList([$calId, array_filter(['singleEvents' => true, 'timeMin' => Dutl::toUTC($minTimeToSync), 'timeMax' => Dutl::toUTC(date('Y-m-d', strtotime($maxTimeToSync . ' + 1 day')))])], $callback);
	        $updatedEvents = [];
	        $updated = 0;
	        $created = 0;
	        $deleted = 0;
	        foreach($sessionsToSync as $session){
	        	$eventDescription = ['start' => ['date' => $session['startdate']], 'end' => ['date' => $session['startdate']], 'summary' => $session['name'], 'description' => $this->googleDescription($session)];
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
	        $eventsToDelete = array_diff(array_keys($existingGoogleEvents), $updatedEvents);
	        foreach ($eventsToDelete as $eventId){
	        	Calendar::deleteEvent($calId, $eventId);
	        	$deleted +=1;
	        }
	        //Feedback::add($this->tr('nbsessionssynchronized') . ': ' . count($sessionsToSync));
	        Feedback::add($this->tr('synchronizationoutcome') . ' - ' . $this->tr('created') . ': ' .  $created . ' - ' . $this->tr('updated') . ': ' . $updated . ' - ' .$this->tr('deleted') . ': ' . $deleted);
	        $this->updateOne(['id' => $id, 'lastsynctime' => date('Y-m-d H:i:s')]);
        }
        return [];
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
    
    public function googleDescription($session){
        $attCols = ['duration' => 'numberUnit',  'intensity' => 'string', 'sport' => 'string', 'stress' => 'string'];
        $contentCols = ['warmup', 'mainactivity', 'warmdown', 'comments'];
        $description = '';
        foreach($attCols as $att => $attType){
        	if (!empty($session[$att])){
        		$description .= '<b>' . $this->tr($att) . '</b>: ' . Utl::format($session[$att], $attType, $this->tr) . '<br>';
        	}
        }
        foreach($contentCols as $att){
        	if (!empty($session[$att])){
        		$description .= '<b>' . $this->tr($att) . '</b>: '. $session[$att] . '<br>';
        	}
        }
        return $description;
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
    
}
?>
