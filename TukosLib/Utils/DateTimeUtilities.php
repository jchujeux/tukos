<?php
namespace TukosLib\Utils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class DateTimeUtilities{

    const dayInSeconds = 24*3600;
    const timeIntervals =  ['year' => 365*self::dayInSeconds, 'quarter' => 90*self::dayInSeconds, 'month' => 30*self::dayInSeconds, 'week' => 7*self::dayInSeconds, 'weekday' => self::dayInSeconds,
    						'day' => self::dayInSeconds, 'hour' => 3600, 'minute' => 60, 'second' => 1];// corresponds to intersection of php strToTime & dojo.date supported intervals
    const daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    
    public static function seconds($duration){
        if (is_string($duration)){
            $duration = json_decode($duration, true);
        }
        return isset($duration[0]) ? $duration[0] * self::timeIntervals[$duration[1]] : 0;
    }
    public static function duration($value, $possibleUnits = ['hour', 'minute'], $round = 'ceiling', $tolerance = 0.01){
		foreach ($possibleUnits as $unit){
			$newValue = $value / self::timeIntervals[$unit];
			if ($newValue - floor($newValue) < $tolerance){
					return json_encode([floor($newValue), $unit]);
			}
		}
    	return json_encode([$round === 'ceiling' ? ceil($newValue) : ($round === 'floor' ? floor($newValue) : $newValue), $unit]);
    }
    public static function toUTC($value){
        return empty($value) ? $value : gmdate(strlen($value) === 9 ? '\TH:i:s\Z' : 'Y-m-d\TH:i:s\Z', strtotime($value));
    }
    public static function fromUTC($value){
        return empty($value) ? $value : gmdate($value[0] === 'T' ? 'H:i:s\Z' : 'Y-m-d H:i:s', strtotime($value));
    }
    public static function toUserDate($value){
    	return empty($value) ? $value : date('Y-m-d', strtotime($value) - Tfk::$registry->timezoneOffset);
    }
    public static function timeToSeconds(/*xxxhh:mm:ss*/$time){
        if ($time){
            $duration = explode(':', substr($time,-8));
            return $duration[0] * self::timeIntervals['hour'] + $duration[1] * self::timeIntervals['minute'] + $duration[2];
        }else{
            return $time;
        }
    }
    public static function secondsToTime(/* should be less than 24 hours*/$seconds){
        $seconds = round($seconds);
        return 'T' . Utl::pad($hours = intval($seconds / self::timeIntervals['hour']), 2) . ':' .  Utl::pad(($seconds - self::timeIntervals['hour']* $hours) / self::timeIntervals['minute'], 2) . ':' . 
                Utl::pad($seconds % self::timeIntervals['minute'], 2);
    }
    public static function timeToMinutes($time){
        return intval(self::timeToSeconds($time) / self::timeIntervals['minute']);
    }
    public static function minutesToTime($minutes){
        return self::secondsToTime(floatval($minutes) * self::timeIntervals['minute']);
    }
    public static function minutesToHHMM($minutes){
        $minutes = round($minutes);
        return Utl::pad(intval($minutes / 60), 2) . ':' .  Utl::pad($minutes % 60, 2);
    }
    public static function mondayThisWeek($ymdDate){
        $dateStamp = strtotime($ymdDate);
        return date('Y-m-d', strtotime(date('w', $dateStamp) == 1 ? 'this monday' : 'previous monday', $dateStamp));
    }
    public static function dayAfter($ymdDate){
        $dateStamp = strtotime($ymdDate);
        return date('Y-m-d', strtotime('+1 day', $dateStamp));
    }
}
?>
