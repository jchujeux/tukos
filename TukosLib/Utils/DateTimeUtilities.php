<?php
namespace TukosLib\Utils;
use TukosLib\TukosFramework as Tfk;

class DateTimeUtilities{

    const dayInSeconds = 24*3600;
    const timeIntervals =  ['year' => 365*self::dayInSeconds, 'quarter' => 90*self::dayInSeconds, 'month' => 30*self::dayInSeconds, 'week' => 7*self::dayInSeconds, 'weekday' => self::dayInSeconds,
    						'day' => self::dayInSeconds, 'hour' => 3600, 'minute' => 60, 'second' => 1];// corresponds to intersection of php strToTime & dojo.date supported intervals
    
    public static function seconds($duration){
        if (is_string($duration)){
            $duration = json_decode($duration, true);
        }
        return $duration[0] * self::timeIntervals[$duration[1]];
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
        return empty($value) ? $value : gmdate('Y-m-d\TH:i:s\Z', strtotime($value));
    }
    public static function fromUTC($value){
        return empty($value) ? $value : date('Y-m-d H:i:s', strtotime($value));
    }
    public static function toUserDate($value){
    	return empty($value) ? $value : date('Y-m-d', strtotime($value) - Tfk::$registry->timezoneOffset);
    }
}
?>
