<?php
namespace TukosLib\Objects\Sports;

use MathPHP\Statistics\Average;
use TukosLib\Utils\Fuzzy as FZ;
use TukosLib\Utils\Utilities as Utl;

class KpisFormulaes {
    
    const thresholdToMax = 0.8;
    private static $b = ['male' => 1.92 * self::thresholdToMax, 'female' => 1.64 * self::thresholdToMax];//, $beta = 2.6, $refCadence = 90;
    
    public static function intensity($rate, $b){
        return $rate * exp($b * ($rate - 1));
    }
    public static function rate($metric, $threshold, $min = 0){
        return ($metric - $min) / ($threshold - $min);
    }
    public static function metricIntensity($metric, $threshold, $sex, $min = 0, $b = null){
        return self::intensity(self::rate($metric, $threshold, $min), is_null($b) ? self::$b[$sex] : $b);
    }
    public static function avgload($metrics, $threshold, $secondsActive, $sex, $min = 0, $b = null){
        return intval($secondsActive / 36 * self::metricIntensity($metrics, $threshold, $sex, $min, $b));
    }
    public static function load($metrics, $threshold, $sex = null, $smoothSeconds = 1, $min = 0, $b = null){//$metrics is an array of the metric value for every seconds of the workout
        $metrics = array_map(function($value){return intval($value);}, $smoothSeconds === 1 ? $metrics : Average::exponentialMovingAverage($metrics, $smoothSeconds));
        $load = 0;
        if (is_array($b)){//to allow for cadence correection of mechanical load
            $count = count($metrics);
            for ($i = 0; $i < $count; $i++){
                $load += self::metricIntensity($metrics[$i], $threshold, $sex, $min, $b[$i]);
            }
        }else{
            $distinctMetrics= array_count_values($metrics);
            foreach ($distinctMetrics as $metric => $secondsActive){
                if ($metric >= $min){
                    $load += $secondsActive * self::metricIntensity($metric, $threshold, $sex, $min, $b);
                }
            }
        }
        return intval($load / 36);
    }
    public static function _timeInZones($metrics, $thresholds, $uncertainty = 3, $fuzzyType = 'absolute', $belowLowest = true, $aboveHighest = true){
        $timeInZones = [];
        for ($i = 0; $i <= count($thresholds); $i++){
            $timeInZones[$i] = 0;
        }
        $fuzzyDomain = FZ::fuzzyDomain($thresholds, $uncertainty, $fuzzyType);
        $distinctMetrics = array_count_values($metrics);
        $highestThreshold = FZ::highestBound($fuzzyDomain);
        $lowestThreshold = FZ::lowestBound($fuzzyDomain);
        foreach ($distinctMetrics as $metric => $secondsActive){
            if (self::contributes($metric, $lowestThreshold, $highestThreshold, $belowLowest, $aboveHighest)){
                $fuzzyValue = FZ::fuzzyValue($metric, $fuzzyDomain);
                foreach($fuzzyValue as $key => $value){
                    $timeInZones[$key] += $secondsActive * $value;
                }
            }
        }
        foreach ($timeInZones as $key => &$time){
            $time = intval($time);
        }
        return $timeInZones;
    }
    public static function timeInZones($metrics, $kpiThresholds, $uncertainty = 3, $fuzzyType = 'absolute', $belowLowest = true, $aboveHighest = true){
        $timeInZones = self::_timeInZones($metrics, $kpiThresholds, $uncertainty, $fuzzyType, $belowLowest, $aboveHighest);
        $last = count($kpiThresholds);
        $result[0] = ['< ' . $kpiThresholds[0], $timeInZones[0]];
        for ($key = 1; $key < $last;  $key++){
            $result[] = ['[' . $kpiThresholds[$key-1] . ', ' . $kpiThresholds[$key] . ']', $timeInZones[$key]];
        }
        $result[$last] = ['> ' . $kpiThresholds[$last-1], $timeInZones[$last]];
        return $result;
    }
    public static function timeAbove($metrics, $kpiThreshold, $uncertainty = 3, $fuzzyType = 'absolute'){
        return self::_timeInZones($metrics, [$kpiThreshold], $uncertainty, $fuzzyType, false, true)[1];
    }
    public static function timeBelow($metrics, $kpiThreshold, $uncertainty = 3, $fuzzyType = 'absolute'){
        return self::_timeInZones($metrics, [$kpiThreshold], $uncertainty, $fuzzyType, true, false)[0];
    }
    public static function _loadInZones($metrics, $zoneThresholds, $intensityThreshold, $sex, $smoothSeconds = 1, $min = 0, $b = null, $uncertainty = 3, $fuzzyType = 'absolute', $belowLowest = true, $aboveHighest = true){
        if ($smoothSeconds > 1){
            $metrics = array_map(function($value){return intval($value);}, Average::exponentialMovingAverage($metrics, $smoothSeconds));
        }
        $loadInZones = [];
        for ($i = 0; $i <= count($zoneThresholds); $i++){
            $loadInZones[$i] = 0;
        }
        $fuzzyDomain = FZ::fuzzyDomain($zoneThresholds, $uncertainty, $fuzzyType);
        $highestThreshold = FZ::highestBound($fuzzyDomain);
        $lowestThreshold = FZ::lowestBound($fuzzyDomain);
        if (is_array($b)){
            foreach ($metrics as $i => $metric){
                if (self::contributes($metric, $lowestThreshold, $highestThreshold, $belowLowest, $aboveHighest)){
                    $fuzzyValue = FZ::fuzzyValue($metric, $fuzzyDomain);
                    $intensity = self::metricIntensity($metric, $intensityThreshold, $sex, $min, $b[i]);
                    foreach($fuzzyValue as $key => $value){
                        $loadInZones[$key] += $intensity * $value;
                    }
                }
            }
        }else{
            $distinctMetrics = array_count_values($metrics);
            foreach ($distinctMetrics as $metric => $secondsActive){
                if (self::contributes($metric, $lowestThreshold, $highestThreshold, $belowLowest, $aboveHighest)){
                    $fuzzyValue = FZ::fuzzyValue($metric, $fuzzyDomain);
                    $intensity = self::metricIntensity($metric, $intensityThreshold, $sex, $min, $b);
                    foreach($fuzzyValue as $key => $value){
                        $loadInZones[$key] += $secondsActive * $intensity * $value;
                    }
                }
            }
        }
        foreach ($loadInZones as $key => &$value){
            $value = intval($value / 36);
        }
        return $loadInZones;
    }
    public static function loadInZones($metrics, $kpiThresholds, $threshold, $sex, $smoothSeconds = 1, $min = 0, $b = null, $uncertainty = 3, $fuzzyType = 'absolute', $belowLowest = true, $aboveHighest = true){
        $loadInZones = self::_loadInZones($metrics, $kpiThresholds, $threshold, $sex, $smoothSeconds, $min, $b, $uncertainty, $fuzzyType, $belowLowest, $aboveHighest);
        foreach ($kpiThresholds as $key => $threshold){
            $result[] = ['< ' . $threshold, $loadInZones[$key]];
        }
        $last = count($kpiThresholds);
        $result[$last] = ['> ' . $kpiThresholds[$last-1], $loadInZones[$last]];
        return $result;
    }
    public static function loadAbove($metrics, $kpiThreshold, $threshold, $sex, $smoothSeconds = 1, $min = 0, $b = null, $uncertainty = 3, $fuzzyType = 'absolute'){
        return self::_loadInZones($metrics, [$kpiThreshold], $threshold, $sex, $smoothSeconds, $min, $b, $uncertainty, $fuzzyType, false, true)[1];
    }
    public static function loadBelow($metrics, $kpiThreshold, $threshold, $sex, $smoothSeconds = 1, $min = 0, $b = null, $uncertainty = 3, $fuzzyType = 'absolute'){
        return self::_loadInZones($metrics, [$kpiThreshold], $threshold, $sex, $smoothSeconds, $min, $b, $uncertainty, $fuzzyType, true, false)[0];
    }
    public static function timeCurve($metrics, $smoothSeconds = 1){
        if ($smoothSeconds > 1){
            $metrics = Average::exponentialMovingAverage($metrics, $smoothSeconds);
        }
        $metrics = array_map(function($value){return intval($value);}, $metrics);
        $distinctMetrics = array_count_values(array_map(['TukosLib\Utils\Utilities', 'nullToZero'], $metrics));
        krsort($distinctMetrics);
        $cumulatedTime = 0; $result = [];
        foreach ($distinctMetrics as $metric => $time){
            $cumulatedTime += $time;
            $result[] = [$cumulatedTime, $metric];
        }
        return json_encode(Utl::array_shrink($result, ['TukosLib\Utils\Utilities', 'minXmaxYWeightShrinkCallback']));
    }
    public static function durationCurve($metrics, $smoothSeconds = 1){
        $totalDuration = count($metrics);
        if ($smoothSeconds > 1){
            $metrics = Average::exponentialMovingAverage($metrics, $smoothSeconds);
        }
        $metrics = array_map(function($value){return intval($value);}, $metrics);
        $result = [];
        $secondsSmooth = 1;
        while ($secondsSmooth < $totalDuration){
            $result[] = [$secondsSmooth, intval(max(Average::exponentialMovingAverage($metrics, $secondsSmooth)))];
            $secondsSmooth = $secondsSmooth * 2;
        }
        $result[] = [$totalDuration, intval(max(Average::exponentialMovingAverage($metrics, $totalDuration)))];
        return json_encode($result);
    }
    public static function shrink($metrics, $shrinkSeconds, $smoothSeconds = 0, $intval){
        $smoothSeconds = $smoothSeconds === 0 ? $shrinkSeconds : $smoothSeconds;
        return json_encode(Utl::array_shrink
            (array_map(function($key, $value) use ($intval){return [$key, $intval ? intval($value) : $value];}, array_keys($metrics), $smoothSeconds === 1 ? $metrics : Average::exponentialMovingAverage($metrics, $smoothSeconds)), 
            ['TukosLib\Utils\Utilities', 'xShrinkCallback'], 
            ['xShrink' => $shrinkSeconds])
        );
    }
    public static function contributes($metric, $lowestThreshold, $highestThreshold, $belowLowest, $aboveHighest){
        return !( !$belowLowest && $metric <= $lowestThreshold || !$aboveHighest && $metric >= $highestThreshold );
    }
}
//KpisFormulaes::init();
?>