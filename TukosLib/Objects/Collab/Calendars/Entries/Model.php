<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Collab\Calendars\Entries;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\ItemsCache;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Google\Calendar;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

	protected $alldayOptions = ['YES', 'NO'];
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'startdatetime'         => 'VARCHAR(30)  DEFAULT NULL',
            'duration'                  => 'VARCHAR(30)  DEFAULT NULL',
            'enddatetime'            => 'VARCHAR(30)  DEFAULT NULL',
            'googlecalid'            => 'VARCHAR(30)  DEFAULT NULL',
        	'periodicity'               => 'VARCHAR(80)  DEFAULT NULL',
            'lateststartdatetime' => 'VARCHAR(30)  DEFAULT NULL',
        	'allday' => "ENUM ('" . implode("','", $this->alldayOptions) . "') DEFAULT NULL",
            'participants' => 'VARCHAR(2048)  DEFAULT NULL',
        	'backgroundcolor' => 'VARCHAR(10) DEFAULT NULL',
        ];
        parent::__construct(
            $objectName, $translator, 'calendarsentries', ['parentid' => Tfk::$registry->get('user')->allowedModules()], ['participants'], $colsDefinition);
        $this->gridsIdCols =  ['participants' => ['participants']];
    }   
    function initialize($init=[]){
        return parent::initialize(array_merge(['duration' => json_encode([1, 'hour'])], $init));
    }
    function getAllExtended($atts){
    	if (isset($atts['where']['#sources'])){
    		$sources = Utl::extractItem('#sources', $atts['where']);
    		$result = [];
    	    if ($sources !== -1){
	    	    if (isset($atts['where']['enddatetime']) && empty($atts['where']['enddatetime'][1])){
	    			unset($atts['where']['enddatetime']);
	    		}
	    	    if (isset($atts['where']['startdatetime']) && empty($atts['where']['startdatetime'][1])){
	    			unset($atts['where']['startdatetime']);
	    		}
	    		foreach ($sources as $source){
	    			if (empty($source['source']) || ! isset($source['visible']) || !$source['visible']){
	    					//Feedback::add($this->tr('sourcenotdefinedignored'));
	    			}else{
		    			if ($source['source'] === 'tukos'){
		    				if (isset($source['where'])){
		    					$atts['where'][] = $source['where'];
		    				}else{
		    					$atts['where']['parentid'] = $source['tukosparent'];
		    				}
		    				if (isset($atts['where']['startdatetime'])){
		    					$atts['where'][] = [SUtl::longFilter('startdatetime', Utl::extractItem('startdatetime', $atts['where'])), ['col' => 'startdatetime', 'opr' => 'IS NULL', 'values' => 'null', 'or' => true]];
		    				}
		    				if (isset($atts['where']['enddatetime'])){
		    					$atts['where'][] = [SUtl::longFilter('enddatetime', Utl::extractItem('enddatetime', $atts['where'])), ['col' => 'enddatetime', 'opr' => 'IS NULL', 'values' => 'null', 'or' => true]];
		    				}
		    				$result = array_merge($result, parent::getAllExtended($atts));
		    			}else{
							if (!isset($optionalArgs)){
								$callback = function($event)use ($source){
									return $this->googleEventToCalendarEntry($event, $source['googleid']);
								};
								$optionalArgs = ['singleEvents' => true];
								if (isset($atts['where']['enddatetime'])){
									$optionalArgs['timeMin'] = Dutl::toUTC($atts['where']['enddatetime'][1]);
								}
								if (isset($atts['where']['startdatetime'])){
									$optionalArgs['timeMax'] = Dutl::toUTC($atts['where']['startdatetime'][1]);
								}
							}
							if (isset($source['where'])){
								foreach ($source['where'] as $where){
									foreach ($where as $property => $values){
										if ($property === 'sharedExtendedProperty'){
											$sharedExtendedProperty = '';
											foreach ($values as $key => $value){
												$sharedExtendedProperty .= $key . '=' . $value;
											}
											if (!empty($sharedExtendedProperty)){
												$optionalArgs['sharedExtendedProperty'] = $sharedExtendedProperty;
											}
										}else{
											$optionalArgs[$property] = $values;
										}
									}
								}
							}
							$result = array_merge($result, array_filter(Calendar::getEventsList([$source['googleid'], $optionalArgs], $callback)));
		    			}
	    			}
	    		}
    	    }
    		return $result;
    	}else{
    		return parent::getAllExtended($atts);
    	}
    }
    
    public function getOne($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
    	if (empty($googleCalId = Utl::getItem('googlecalid', $atts['where']))){
    		return parent::getOne($atts, $jsonColsPaths, $jsonNotFoundValue);
    	}else{
    		return $this->getGoogleEvent($googleCalId, $atts['where']['id']);
    	}
    }
    
    public function insert($values, $init = false, $jsonFilter = false, $reference = null){
    	if (empty($values['googlecalid']) || empty($values['startdatetime'])){
    		return parent::insert($values, $init, $jsonFilter, $reference);
    	}else{
    		return $this->insertGoogleEvent($values);
    	}
    }
    
    public function insertGoogleEvent($values){
    	return $this->googleEventToCalendarEntry(Calendar::createEvent($values['googlecalid'], $this->calendarEntryToGoogleEvent($values)), $values['googlecalid']);
    }
    
	public static function setExtendedProperties(&$eventProperties, $values){
		$extendedShared = [];
		$extendedProperties = ['parentid', 'contextid'];
		foreach ($extendedProperties as $property){
			if (!empty($values[$property])){
				$extendedShared['tukos' . $property] = $values[$property];
			}
			if (!empty($extendedShared['tukosparentid'])){
				$grandParent = SUtl::$tukosModel->getOne(['where' => ['id' => $extendedShared['tukosparentid']], 'cols' => ['parentid']]);
				if (!empty($grandParent)){
					$extendedShared['tukosgrandparentid'] = $grandParent['parentid'];
				}
			}
		}
		if (!empty($extendedShared)){
			//$eventProperties['extendedProperties'] = ['shared' => ['tukos' => json_encode($extendedShared)]];
			$eventProperties['extendedProperties'] = ['shared' => $extendedShared];
		}
	}
    
    public function getGoogleEvent($calId, $id, $rogooglecalid = true){
    	return ItemsCache::insert($this->googleEventToCalendarEntry(Calendar::getEvent($calId, $id), $calId, $rogooglecalid));
    }

    protected function isTukosEvent($id){
    	return Utl::is_integer($id) && $id < 1000000;
    }
    
    public function updateOne($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
    	$id = $newValues['id'];
    	if ($this->isTukosEvent($id)){//was a tukos calendar entry
    		if (!empty($newValues['startdatetime']) && !empty($newValues['googlecalid'])){// transform into a google event
    			$event = $this->insertGoogleEvent(array_merge(parent::getOne(['where' => ['id' => $id], 'cols' => ['parentid', 'name', 'comments', 'backgroundcolor', 'contextid']]), $newValues));
    			parent::delete(['id' => $id]/*, $newValues*/);
    			return $event;
    		}else{
    			return parent::updateOne($newValues, $atts, $insertIfNoOld, $jsonFilter);
    		}
    	}else{//was a google event
    		$calId = Utl::extractItem('rogooglecalid', $newValues);
    		if (isset($newValues['startdatetime']) && empty($newValues['startdatetime'])){//transform into a tukos calendar entry
    			Utl::extractItems(['id', 'startdatetime', 'enddatetime'], $newValues);
    			$googleEvent = $this->getGoogleEvent($calId, $id, false);
    			Utl::extractItems(['id', 'startdatetime', 'enddatetime'], $googleEvent);
    			$inserted = parent::insert(array_merge($googleEvent, $newValues), true);
    			Calendar::deleteEvent($calId, $id);
    			return $inserted;
    		}else if ($newValues['googlecalid'] != $calId){//move event to a different google calendar
    			$event = $this->getGoogleEvent($calId, $id);
    			$newEvent = $this->insertGoogleEvent($newValues);
    			Calendar::deleteEvent($calId, $id);
    			return $newEvent;
    		}else{
    			return Calendar::updateEvent($newValues['googlecalid'], $id, $this->calendarEntryToGoogleEvent($newValues));
    		}
    	}
    }

    public function delete ($where, $item = []){
    	if ($this->isTukosEvent($where['id'])){
    		return parent::delete($where, $item);
    	}else{
    		Calendar::deleteEvent($item['rogooglecalid'], $where['id']);
    		return $where['id'];
    	}
    }
    
    public function googleEventToCalendarEntry($event, $calId, $rogooglecalid = true){
    	$tukosSharedExtended = function($googleSharedExtended){
    		$result = [];
    		foreach ($googleSharedExtended as $key => $value){
    			if (strlen($key) > 5 && strpos($key, 'tukos') === 0){// legacy of 'tukos' and json_encode of all tukos params
    				$result[substr($key, 5)] = $value;
    			}
    		}
    		return $result;
    	};
    	if ($event->status === "cancelled"){
    		return false;
    	}else{
	    	$start = $event->getStart()->dateTime;
	    	if (is_null($start)){
	    		$start = $event->getStart()->date;
	    		$duration = '[24,"hour"]';
	    		$end = date('Y-m-d H:i:s', strtotime($start) + 24 * 3600);
	    		$allDay = 'YES';
	    	}else{
		    	$end = $event->getEnd()->dateTime;
		    	$duration = Dutl::duration(strtotime($end) - strtotime($start));
		    	$allDay = 'NO';
	    	}
	    	$extended = $event->getExtendedProperties();
	    	if (isset($extended['shared'])){
	    		$extendedValues = $tukosSharedExtended($extended['shared']);
	    	}else{
	    		$extendedValues = [];
	    	}
	    	$result = array_merge(
	    		['id' => $event->getId(), 'name' => $event->getSummary(), 'googlecalid' => $calId, 'comments' => $event->getDescription(), 'created' => Dutl::toUTC($event->getCreated()), 'startdatetime' => Dutl::toUTC($start),
	    		 'enddatetime' => Dutl::toUTC($end), 'duration' => $duration, 'backgroundcolor' => Calendar::eventBackgroundColor($event, $calId), 'allday' => $allDay],
	    		$extendedValues
	    	);
	    	if ($rogooglecalid){
	    		$result['rogooglecalid'] = $calId;
	    	}
	    	return $result;
    	}
    }
    public function calendarEntryToGoogleEvent($item){
    	$tukosToGoogle = array_merge(
    			Utl::getItem('allday', $item) === 'YES' 
    				? ['startdatetime' => ['start' => ['date' => ['TukosLib\Utils\DateTimeUtilities', 'toUserDate']]], 'enddatetime' => ['end' => ['date' => ['TukosLib\Utils\DateTimeUtilities', 'toUserDate']]]]
    				: ['startdatetime' => ['start' => ['dateTime' => ['TukosLib\Utils\DateTimeUtilities', 'toUTC']]], 'enddatetime' => ['end' => ['dateTime' => ['TukosLib\Utils\DateTimeUtilities', 'toUTC']]]],
    			['name' => 'summary', 'comments' => 'description', 'backgroundcolor' => ['colorId' => ['TukosLib\Google\Calendar', 'getEventColorId']]]
    	);
    	$valuesToProcess = array_intersect_key($item, $tukosToGoogle);
    	$eventProperties = [];
    	foreach ($valuesToProcess as $field => $value){
    		$this->setEventProperty($eventProperties, $tukosToGoogle[$field], $value);
    	}
    	$this->setExtendedProperties($eventProperties, $item);
    	return $eventProperties;
    }
    protected static function toParentId($value){
    	return ['parentid' => $value];
    }
    protected static function toContextId($value){
    	return ['contextid' => $value];
    }
    
    public function setEventProperty(&$event, $description, $value){
    	if (is_array($description)){
    		reset($description);
    		$propertyName = key($description);
    		$details = $description[$propertyName];
    		reset($details);
    		$subProperty = key($details);
    		if (is_string($subProperty)){
    			$event[$propertyName] = [$subProperty => call_user_func($details[$subProperty], $value)];
    		}else{
    			$event[$propertyName] = call_user_func($details, $value);
    		}
    	}else{
    		$event[$description] = $value;
    	}
    }
}
?>
