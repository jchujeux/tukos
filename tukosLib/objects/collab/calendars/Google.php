<?php
namespace TukosLib\Objects\Collab\Calendars;

use TukosLib\Google\Client;
use TukosLib\Utils\Utilities as Utl;
class Google{
    private static $service = null, $googleColors, $calendarColors, $eventColors, $calendarsList, $calendarsById;
	public static function getService(){
		if (is_null(self::$service)){
			self::$service = new \Google_Service_Calendar(Client::get());
			self::$googleColors = self::$service->colors->get();
	    	self::$calendarColors = self::$googleColors->getCalendar();
			self::$eventColors = self::$googleColors->getEvent();
		}
		return self::$service;
    }

    public static function getCalendarsList($callback = null){
    	if (!isset(self::$calendarsList)){
    		self::$calendarsList = Client::getList(self::getService(),'calendarList', 'listCalendarList');
    	}
    	return $callback === null ? self::$calendarsList : array_map($callback, self::$calendarsList);
    }
    
    public static function getEventColorId($backgroundColor){
    	if (!empty($backgroundColor)){
    		self::getService();
    		foreach (self::$eventColors as $colorId => $colors){
    			if ($backgroundColor == $colors['background']){
    				return $colorId;
    			}
    		}
    		$rgb = Utl::hexToRgb($backgroundColor);
    		$distance = 3 * 255;
    		foreach(self::$eventColors as $colorId => $colors){
    			$testRgb = Utl::hexToRgb($colors['background']);
    			$newDistance = abs($rgb['red'] - $testRgb['red']) + abs($rgb['green'] - $testRgb['green']) + abs($rgb['blue'] - $testRgb['blue']);
    			if ($newDistance < $distance){
    				$distance = $newDistance;
    				$closiestMatch = $colorId; 
    			}
    		}
    		return $closiestMatch;
    	}
    	return null;
    }
        
    public static function getCalendar($id){
    	return self::getService()->calendars->get($id);
    }
    
    public static function getEventsList($args, $callback = null){
    	return Client::getList(self::getService(),'events', 'listEvents', $callback, $args);
    }
    public static function getRules($args){
    	$callback = function($rule){
    		return ['id' => $rule->getId(), 'email' => $rule->getScope()->getValue(), 'role' => $rule->getRole()];
    	};
    	return Client::getList(self::getService(),'acl', 'listAcl', $callback, $args);
    }
    
    public static function updateRules($args){
    	$calId = $args['googlecalid']; 
    	$service = self::getService(); $newRules = [];
    	$existingRules = Utl::toAssociative((array) self::getRules([$calId]), 'id');
    	$modifiedRules = Utl::toAssociative(
    		array_filter($args['acl'], function($rule) use (&$newRules){
	    		if (empty($rule['id'])){
	    			$newRules[] = $rule;
	    			return false;
	    		}else{
	    			return true;
	    		}
	    	}),
	    	'id'
    	);
    	$rulesToDelete = array_diff_key($existingRules, $modifiedRules);
    	foreach ($rulesToDelete as $ruleId => $rule){
    		$service->acl->delete($calId, $ruleId);
    	}
    	$scope = new \Google_Service_Calendar_AclRuleScope();
    	foreach ($modifiedRules as $ruleId => $item){
    		if (!empty(array_diff_assoc($item, ($existingRule = $existingRules[$ruleId])))){
				$rule = $service->acl->get($calId, $ruleId);
				if ($item['role'] !== $existingRule['role']){
					$rule->setRole($item['role']);
				}
				if ($item['email'] !== $existingRule['email']){
    				$scope->setType('user');
    				$scope->setValue($item['email']);
    				$rule->setScope($scope);
				}
    			$service->acl->update($calId, $rule->getId(), $rule);
    		}
    	}
    	$rule = new \Google_Service_Calendar_AclRule();
    	foreach ($newRules as $item){
    		$scope->setType('user');
    		$scope->setValue($item['email']);
    		$rule->setScope($scope);
    		$rule->setRole($item['role']);
    		$service->acl->insert($calId, $rule);
    	}
    }
    
    public static function createCalendar($description, $rules = []){
    	$service = self::getService();
    	$calendar = $service->calendars->insert(new \Google_Service_Calendar_Calendar($description));
    	if (!empty($rules)){
    		$calId = $calendar->getId();
    		$rule = new \Google_Service_Calendar_AclRule();
    		$scope = new \Google_Service_Calendar_AclRuleScope();
    		foreach ($rules as $item){
    			$scope->setType('user');
    			$scope->setValue($item['email']);
    			$rule->setScope($scope);
    			$rule->setRole($item['role']);
    			$service->acl->insert($calId, $rule);
    		}
    	}
    	return $calendar;
    }
    
    public static function deleteCalendar($calId){
    	self::getService()->calendars->delete($calId);
    }
    
    
    public static function createEvent($calId, $event){
    	return self::getService()->events->insert($calId, new \Google_Service_Calendar_Event($event));
    }
    
    public static function eventBackgroundColor($event, $calendarId){
    	self::getService();
    	$colorId = $event->getColorId();
    	if (!empty($colorId)){
    		return self::$eventColors[$colorId]['background'];
    	}else{
    		return '';//self::getCalendar($calendarId)->getBackgroundColor();
    	}
    }
    
    public static function eventExtended($event, $calendarId, $extendedProperty){
    	self::getService();
    	$extendedProperties = $event->getExtendedProperties();
    	if (isset($extendedProperties['shared'][$extendedProperty])){
    		return $extendedProperties['shared'][$extendedProperty];
    	}else{
    		return null;
    	}
    }
    
    public static function getEvent($calId, $eventId){
    	return self::getService()->events->get($calId, $eventId);
    }

    public static function updateEvent($calId, $eventId, $eventUpdate){
    	$service = self::getService();
    	$event = $service->events->get($calId, $eventId);
    	$event = Utl::array_merge_recursive_replace($event, $eventUpdate);
    	return self::getService()->events->update($calId, $eventId, $event);
    }
    
    public static function deleteEvent($calId, $eventId){
    	$service = self::getService();
    	$service->events->delete($calId, $eventId);
    }
    
}
?>