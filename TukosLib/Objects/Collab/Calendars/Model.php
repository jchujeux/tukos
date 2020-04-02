<?php

namespace TukosLib\Objects\Collab\Calendars;

use TukosLib\Objects\AbstractModel;
use TukosLib\Google\Calendar;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

	protected $sourceOptions    = ['tukos', 'google'];
	
	function __construct($objectName, $translator=null){
        $colsDefinition = [
        		'sources' =>  'longtext',
        		'periodstart' => 'VARCHAR(20) DEFAULT NULL',
        		'periodend'   => 'VARCHAR(20) DEFAULT NULL',
        		'weeksbefore'   => 'INT(11) DEFAULT NULL',
        		'weeksafter'   => 'INT(11) DEFAULT NULL',
        ];
        parent::__construct($objectName, $translator, 'calendars', ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects()], ['sources'], $colsDefinition, [], [], ['worksheet', 'custom']);
        $this->gridsIdCols = array_merge($this->gridsIdCols, ['sources' => ['tukosparent']]);
    }

    public function initializeExtended($init = []){
    	$googleSource = Utl::extractItem('googleSource', $init);
    	if (empty($googleSource)){
    		$paneMode = isset($this->paneMode) ? $this->paneMode : 'Tab';
    		$googleSource = $this->user->getCustomView($this->objectName, 'edit', $paneMode, ['widgetsDescription', 'calendartab', 'atts', 'defaultCalendar']);
    	}
    	$tukosSource = Utl::extractItem('tukosSource', $init);
    	$id = 1;
    	$init['parentid'] = $tukosSource;
    	$init['sources'] = empty($googleSource) 
    		?  [['id' => 1, 'rowId' => 1, 'source' => 'tukos', 'tukosparent' => $tukosSource, 'selected' => 'on']]
    		:  [['id' => 1, 'rowId' => 1, 'source' => 'google', 'googleid' => $googleSource, 'selected' => 'on'], ['id' => 2, 'rowId' => 2, 'source' => 'tukos', 'tukosparent' => $tukosSource]];
    	return parent::initializeExtended($init);
    }
    
    function initialize($init=[]){
        return parent::initialize(array_merge(['periodstart' => date('Y-m-d', strtotime('last monday'))], $init));
    }
    
    public function getOneExtended ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $item = parent::getOneExtended($atts, $jsonColsPaths, $jsonNotFoundValue);
    	if (!empty($weeksBefore = Utl::getItem('weeksbefore', $item))){
			$item['periodstart'] = date('Y-m-d', strtotime('last monday') - $weeksBefore * 7 * 24 * 3600);
		}
    	if (!empty($weeksAfter = Utl::getItem('weeksafter', $item))){
			$item['periodend'] = date('Y-m-d', strtotime('next sunday') + $weeksAfter *  (7 * 24) * 3600);
		}
		$item['calendar'] = '';
   		if (!empty($item['sources'])){
			$googleIds = array_filter(array_column($item['sources'], 'googleid'));
			if (!empty($googleIds)){
				$googleCalendars = $this->calendarsGetAll();
				foreach ($googleCalendars as $calendar){
					Tfk::addExtra($calendar['id'], ['name' => $calendar['name'], 'label' => $calendar['name'] . ' (' . $calendar['id'] . ')']);
				}
				$googleCalendars = Utl::toAssociative($googleCalendars, 'id');
				forEach ($item['sources'] as &$source){
					if (isset($source['googleid'])){
						$id = $source['googleid'];
						if (isset($googleCalendars[$id])){
							$source['backgroundcolor'] = $googleCalendars[$id]['backgroundColor'];
						}else{
							Feedback::add($this->tr('googlecalendarnotfound: ' . $id));
						}
					}
				}
			}
		}
		return $item;
    }

    function calendarsGetAll($query = []){
    	$calendarIdName = function($item){
    		return ['id' => $item->getId(), 'name' => $item->getSummary(), 'backgroundColor' => $item->getBackgroundColor()];
    	};
    	return Calendar::getCalendarsList($calendarIdName);
    }

    function calendarsSelect($query = []){
    	$calendarIdName = function($item){
    		$id = $item->getId();
    		$name = $item->getSummary();
    		return ['id' => $id, 'name' => $name . ' (' . $id . ')'];
    	};
    	return array_merge([['id' => '', 'name' => '']], Calendar::getCalendarsList($calendarIdName));
    }
    function calendarSelect($query){
    	$calendarId = $query['where']['id'];
    	if (empty($calendarId)){
    		return ['id' => '', 'name' => ''];
    	}else{
    		return ['id' => $calendarId, 'name' => Calendar::getCalendar($calendarId)->getSummary() . ' (' . $calendarId . ')'];
    	}
    }
    
    function getGoogleCalendarIdChanged($atts){
    	return ["calendarentries" => []];
    }
}
?>
