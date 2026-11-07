<?php
namespace TukosLib\Objects\Sports;

use MathPHP\Statistics\Average;
use TukosLib\Utils\Fuzzy as FZ;
use TukosLib\Utils\Utilities as Utl;

class KpisFormulaes {
    
    const thresholdToMax = 0.8;
    private static $b = ['male' => 1.92 * self::thresholdToMax, 'female' => 1.64 * self::thresholdToMax];//, $beta = 2.6, $refCadence = 90;
    
    private static function valueToString($value, $precision){
        if ($value === null){
            return "0";
        }else{
            return strval(round($value, $precision));
        }
    }
    public static function intensity($rate, $b){
        return $rate * exp($b * ($rate - 1));
    }
    public static function rate($metric, $threshold, $min = 0){
        return ($metric - $min) / ($threshold - $min);
    }
    public static function metricIntensity($metric, $threshold, $sex, $min = 0, $b = null){
        return self::intensity(self::rate($metric, $threshold, $min), is_null($b) ? self::$b[$sex] : $b);
    }
    public static function avgload($metrics, $threshold, $secondsActive, $sex, $min = 0, $b = null, $precision = 0){
        return round($secondsActive / 36 * self::metricIntensity($metrics, $threshold, $sex, $min, $b), $precision);
    }
    public static function load($metrics, $threshold, $sex = null, $smoothSeconds = 1, $min = 0, $b = null, $precision = 0){//$metrics is an array of the metric value for every seconds of the workout
        $metrics = array_map(function($value) use ($precision){return self::valueToString($value, $precision);}, $smoothSeconds === 1 ? $metrics : Average::exponentialMovingAverage($metrics, intval($smoothSeconds)));
        $load = 0;
        if (is_array($b)){//to allow for cadence correection of mechanical load
            $count = count($metrics);
            for ($i = 0; $i < $count; $i++){
                $load += self::metricIntensity(floatval($metrics[$i]), $threshold, $sex, $min, $b[$i]);
            }
        }else{
            $distinctMetrics = array_count_values($metrics);
            foreach ($distinctMetrics as $metric => $secondsActive){
                $metric = floatval($metric);
                if ($metric >= $min){
                    $load += $secondsActive * self::metricIntensity($metric, $threshold, $sex, $min, $b);
                }
            }
        }
        return round($load / 36, $precision);
    }
    public static function estimatedpower_avg($metrics, $secondsActive, $elevationGain, $weight, $extraWeight = 0.0, $frictionCoef = 0.0, $dragCoef = 0.0, $windVelocity = 0.0, $precision = 0){
        if (empty($metrics) || empty($secondsActive) || empty($elevationGain)){
            return false;
        }else{
            $gravity = 10; $distance = $metrics * 1000; $velocity = $distance / $secondsActive; $slopeRatio = $elevationGain / $distance; $totalWeightForce = ((float)$weight + (float)$extraWeight) * $gravity;
            $frictionCoef = (float)$frictionCoef; $dragCoef = (float)$dragCoef; $windVelocity = (float)$windVelocity;
            $frictionForce = $frictionCoef * $totalWeightForce;
            $gravityForce = $slopeRatio * $totalWeightForce;
            $dragForce = $dragCoef * ($velocity - $windVelocity) ** 2;
            return round(($frictionForce + $gravityForce + $dragForce) * $velocity, $precision);
        }
    }
    /*public static function estimatedWattsStream($metrics, $grade_smoothstream, $weight, $ftp, $extraWeight = 0.0, $frictionCoef = 0.0, $dragCoef = 0.0, $windVelocity = 0.0, $precision = 0){
     if (empty($metrics) || empty($grade_smoothstream)){
     return false;
     }else{
     $wattsStream = [];
     $gravity = 10; $velocity_smoothStream = $metrics; $totalMass = (float)$weight + (float)$extraWeight; $totalWeightForce = $totalMass * $gravity;
     $frictionCoef = (float)$frictionCoef; $dragCoef = (float)$dragCoef; $windVelocity = (float)$windVelocity;
     $frictionForce = $frictionCoef * $totalWeightForce;
     $previousVelocity = 0.0;
     $velocity_smoothStream = Average::exponentialMovingAverage($velocity_smoothStream, 30);
     $grade_smoothstream = Average::exponentialMovingAverage($grade_smoothstream, 30);
     foreach($velocity_smoothStream as $key => $velocity){
     $dragForce = $dragCoef * ($velocity - $windVelocity) ** 2;
     $gravityForce = $grade_smoothstream[$key] * $totalWeightForce / 100;
     $accelerationForce = ($velocity - $previousVelocity) * $totalMass;
     $wattsStream[$key] = round(max(0, ($frictionForce + $gravityForce + $dragForce + $accelerationForce) * $velocity), $precision);
     $previousVelocity = $velocity;
     }
     return $wattsStream;
     }
     }*/
    public static function estimatedRawWattsStream($metrics, $altitudestream, $latitudestream, $longitudestream,  $weight, $ftp, $extraWeight = 0.0, $frictionCoef = 0.0, $dragCoef = 0.0, $windVelocity = 0.0, $windDirection = '0', $precision = 0){
        if (empty($metrics) || empty($altitudestream)){
            return false;
        }else{
            $wattsStream = [];
            $gravity = 10; $distancestream = $metrics; $totalMass = (float)$weight + (float)$extraWeight; $totalWeightForce = $totalMass * $gravity;
            $frictionCoef = (float)$frictionCoef; $dragCoef = (float)$dragCoef; $windVelocity = (float)$windVelocity;
            $frictionForce = $frictionCoef * $totalWeightForce;
            $distancestream = Average::exponentialMovingAverage($distancestream, 30);
            $altitudestream = Average::exponentialMovingAverage($altitudestream, 30);
            $latitudestream = Average::exponentialMovingAverage($latitudestream, 30);
            $longitudestream = Average::exponentialMovingAverage($longitudestream, 30);
            $previousVelocity = 0.0;
            $previousDistance = $distancestream[0];
            $previousAltitude = $altitudestream[0];
            $previousLatitude = $latitudestream[0];
            $previousLongitude = $longitudestream[0];
            if (!empty($windDirection)){
                $windAngle = $windDirection * 2 * pi() / 16;
            }else{
                $axialWindFactor = 1.0;
            }
            $length = count($distancestream);
            $wattsStream[0] = 0;
            $previousDistance = $distancestream[0];
            $previousLatitude = $latitudestream[0];
            $previousLongitude = $longitudestream[0];
            for ($key = 1; $key < $length; $key++){
                $distance = $distancestream[$key];
                $velocity = $distance - $previousDistance;
                $altitude = $altitudestream[$key];
                $latitude  = $latitudestream[$key];
                $longitude = $longitudestream[$key];
                $deltaY = ($longitude - $previousLongitude) * cos($latitude);
                $deltaX = $latitude - $previousLatitude;
                if ($velocity > 1.0){
                    if (!empty($windDirection)){
                        $directionAngle = atan2($deltaY, $deltaX);
                        $axialWindFactor = cos($directionAngle - $windAngle);
                    }
                    $slope = ($altitude - $previousAltitude) /  $velocity;
                    $dragForce = $dragCoef * ($velocity + $windVelocity * $axialWindFactor) ** 2;
                    $gravityForce = $slope * $totalWeightForce;
                    $accelerationForce = ($velocity - $previousVelocity) * $totalMass;
                    $watts = ($frictionForce + $gravityForce + $dragForce + $accelerationForce) * $velocity;
                    $wattsStream[$key] = round(min(max(0, $watts), $ftp * 3), $precision);
                }else{
                    $wattsStream[$key] = 0;
                }
                $previousVelocity = $velocity;
                $previousDistance = $distance;
                $previousAltitude = $altitude;
                $previousLatitude = $latitude;
                $previousLongitude = $longitude;
            }
            return $wattsStream;
        }
    }
    public static function _timeInZones($metrics, $thresholds, $smoothSeconds = 1, $uncertainty = 3, $fuzzyType = 'absolute', $belowLowest = true, $aboveHighest = true, $precision = 0){
        if ($smoothSeconds > 1){
            $metrics = Average::exponentialMovingAverage($metrics, intval($smoothSeconds));
        }
        $metrics = array_map(function($value) use ($precision) {return self::valueToString($value, $precision);}, $metrics);
        $timeInZones = [];
        for ($i = 0; $i <= count($thresholds); $i++){
            $timeInZones[$i] = 0;
        }
        $fuzzyDomain = FZ::fuzzyDomain($thresholds, $uncertainty, $fuzzyType);
        $distinctMetrics = array_count_values($metrics);
        $highestThreshold = FZ::highestBound($fuzzyDomain);
        $lowestThreshold = FZ::lowestBound($fuzzyDomain);
        foreach ($distinctMetrics as $metric => $secondsActive){
            $metric = floatval($metric);
            if (self::contributes($metric, $lowestThreshold, $highestThreshold, $belowLowest, $aboveHighest)){
                $fuzzyValue = FZ::fuzzyValue($metric, $fuzzyDomain);
                foreach($fuzzyValue as $key => $value){
                    $timeInZones[$key] += $secondsActive * $value;
                }
            }
        }
        foreach ($timeInZones as $key => &$time){
            $time = round($time, $precision);
        }
        return $timeInZones;
    }
    public static function timeInZones($metrics, $kpiThresholds, $smoothSeconds = 1, $uncertainty = 3, $fuzzyType = 'absolute', $belowLowest = true, $aboveHighest = true, $precision = 0){
        $timeInZones = self::_timeInZones($metrics, $kpiThresholds, $smoothSeconds, $uncertainty, $fuzzyType, $belowLowest, $aboveHighest, $precision);
        $last = count($kpiThresholds);
        $result[0] = ['< ' . $kpiThresholds[0], $timeInZones[0]];
        for ($key = 1; $key < $last;  $key++){
            $result[] = ['[' . $kpiThresholds[$key-1] . ', ' . $kpiThresholds[$key] . ']', $timeInZones[$key]];
        }
        $result[$last] = ['> ' . $kpiThresholds[$last-1], $timeInZones[$last]];
        return $result;
    }
    public static function timeAbove($metrics, $kpiThreshold, $smoothSeconds = 1, $uncertainty = 3, $fuzzyType = 'absolute', $precision = 0){
        return self::_timeInZones($metrics, [$kpiThreshold], $smoothSeconds, $uncertainty, $fuzzyType, false, true, $precision)[1];
    }
    public static function timeBelow($metrics, $kpiThreshold, $smoothSeconds, $uncertainty = 3, $fuzzyType = 'absolute', $precision = 0){
        return self::_timeInZones($metrics, [$kpiThreshold], $smoothSeconds, $uncertainty, $fuzzyType, true, false, $precision)[0];
    }
    public static function _loadInZones($metrics, $zoneThresholds, $intensityThreshold, $sex, $smoothSeconds = 1, $min = 0, $b = null, $uncertainty = 3, $fuzzyType = 'absolute', $belowLowest = true, $aboveHighest = true, $precision = 0){
        if ($smoothSeconds > 1){
            $metrics = Average::exponentialMovingAverage($metrics, intval($smoothSeconds));
        }
        $metrics = array_map(function($value) use ($precision) {return self::valueToString($value, $precision);}, $metrics);
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
                $metric = floatval($metric);
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
            $value = round($value / 36, $precision);
        }
        return $loadInZones;
    }
    public static function _advancedLoadInZones($metrics, $metricsZoneThreshold, $loadMetrics, $loadIntensityThreshold, $sex, $smoothSeconds = 1, $min = 0, $b = null, $uncertainty = 3, $fuzzyType = 'absolute', $belowLowest = true, $aboveHighest = true, $precision = 0){
        if ($smoothSeconds > 1){
            $loadMetrics = Average::exponentialMovingAverage($loadMetrics, intval($smoothSeconds));
            $metrics = Average::exponentialMovingAverage($metrics, intval($smoothSeconds));
        }
        $loadInZones = [];
        for ($i = 0; $i <= count($metricsZoneThreshold); $i++){
            $loadInZones[$i] = 0;
        }
        $fuzzyDomain = FZ::fuzzyDomain($metricsZoneThreshold, $uncertainty, $fuzzyType);
        $highestThreshold = FZ::highestBound($fuzzyDomain);
        $lowestThreshold = FZ::lowestBound($fuzzyDomain);
        foreach ($metrics as $i => $metric){
            if (self::contributes($metric, $lowestThreshold, $highestThreshold, $belowLowest, $aboveHighest)){
                $fuzzyValue = FZ::fuzzyValue($metric, $fuzzyDomain);
                $intensity = self::metricIntensity($loadMetrics[$i], $loadIntensityThreshold, $sex, $min, is_array($b) ? $b[$i] : $b);
                foreach($fuzzyValue as $key => $value){
                    $loadInZones[$key] += $intensity * $value;
                }
            }
        }
        foreach ($loadInZones as $key => &$value){
            $value = round($value / 36, $precision);
        }
        return $loadInZones;
    }
    public static function loadInZones($metrics, $kpiThresholds, $loadMetrics, $threshold, $sex, $smoothSeconds = 1, $min = 0, $b = null, $uncertainty = 3, $fuzzyType = 'absolute', $belowLowest = true, $aboveHighest = true, $precision = 0){
        $loadInZones = self::_advancedloadInZones($metrics, $kpiThresholds, $loadMetrics, $threshold, $sex, $smoothSeconds, $min, $b, $uncertainty, $fuzzyType, $belowLowest, $aboveHighest);
        $last = count($kpiThresholds);
        $result[0] = ['< ' . $kpiThresholds[0], $loadInZones[0]];
        for ($key = 1; $key < $last;  $key++){
            $result[] = ['[' . $kpiThresholds[$key-1] . ', ' . $kpiThresholds[$key] . ']', $loadInZones[$key]];
        }
        $result[$last] = ['> ' . $kpiThresholds[$last-1], $loadInZones[$last]];
        return $result;
    }
    public static function loadAbove($metrics, $kpiThreshold, $loadMetrics, $threshold, $sex, $smoothSeconds = 1, $min = 0, $b = null, $uncertainty = 3, $fuzzyType = 'absolute', $precision = 0){
        return self::_advancedloadInZones($metrics, [$kpiThreshold], $loadMetrics, $threshold, $sex, $smoothSeconds, $min, $b, $uncertainty, $fuzzyType, false, true, $precision)[1];
    }
    public static function loadBelow($metrics, $kpiThreshold, $loadMetrics, $threshold, $sex, $smoothSeconds = 1, $min = 0, $b = null, $uncertainty = 3, $fuzzyType = 'absolute', $precision = 0){
        return self::_advancedloadInZones($metrics, [$kpiThreshold], $loadMetrics, $threshold, $sex, $smoothSeconds, $min, $b, $uncertainty, $fuzzyType, true, false, $precision)[0];
    }
    public static function timeCurve($metrics, $smoothSeconds = 1, $precision = 0){
        if ($smoothSeconds > 1){
            $metrics = Average::exponentialMovingAverage($metrics, intval($smoothSeconds));
        }else{
            $metrics = Average::simpleMovingAverage($metrics, 2);
        }
        $metrics = array_map(function($value) use ($precision){return self::valueToString($value, $precision);}, $metrics);
        $distinctMetrics = array_count_values($metrics);
        krsort($distinctMetrics);
        $cumulatedTime = 0; $result = [];
        $firstTime = reset($distinctMetrics);
        if ($firstTime > 1){
            $result[] = [1, array_key_first($distinctMetrics)];
        }
        foreach ($distinctMetrics as $metric => $time){
            $cumulatedTime += $time;
            $result[] = [$cumulatedTime, $metric];
        }
        return json_encode(Utl::array_shrink($result, ['TukosLib\Utils\Utilities', 'minXmaxYWeightShrinkCallback']));
    }
    /*    public static function durationCurve($metrics, $smoothSeconds = 1){
     $totalDuration = count($metrics);
     if ($smoothSeconds > 1){
     $metrics = Average::exponentialMovingAverage($metrics, intval($smoothSeconds));
     }
     $metrics = array_map(function($value){return intval($value);}, $metrics);
     $result = [];
     $secondsSmooth = 1;
     while ($secondsSmooth < $totalDuration){
     $result[] = [$secondsSmooth, intval(max(Average::exponentialMovingAverage($metrics, intval($secondsSmooth))))];
     $secondsSmooth = $secondsSmooth * 2;
     }
     $result[] = [$totalDuration, intval(max(Average::exponentialMovingAverage($metrics, $totalDuration)))];
     return json_encode($result);
     }*/
    public static function longestDuration($threshold, $values){
        $duration = 0; $i = 0; $totalDuration = count($values);
        while ($i < $totalDuration){
            while ($i < $totalDuration && $values[$i] < $threshold){
                $i +=1;
            }
            if ($i == $totalDuration){
                return $duration;
            }
            $newDuration = 0;
            while ($i < $totalDuration && $values[$i] >= $threshold){
                $newDuration += 1;
                $i +=1;
            }
            $duration = max($duration, $newDuration);
            if ($i == $totalDuration){
                return $duration;
            }
        }
        return $duration;
        
    }
    public static function durationCurve($metrics, $smoothSeconds = 30, $precision = 0){
        $totalDuration = count($metrics);
        $smoothSeconds = min($smoothSeconds, $totalDuration);
        $metrics = array_map(function($value) use ($precision){return round($value, $precision);}, $metrics);
        $result = [];
        $lastSmoothedValues = $metrics;
        $lastSmoothedValue = $maxValue = max($lastSmoothedValues);
        $secondsSmooth = 1;
        $result[] = [$secondsSmooth, $lastSmoothedValue];
        if ($smoothSeconds > 1){
            $secondsSmooth = 2;
            while ($secondsSmooth <= $smoothSeconds){
                $lastSmoothedValues = Average::exponentialMovingAverage($metrics, $secondsSmooth);
                $lastSmoothedValue = round(max($lastSmoothedValues), $precision);
                $result[] = [$secondsSmooth, $lastSmoothedValue];
                if ($secondsSmooth === $smoothSeconds){
                    break;
                }
                $secondsSmooth = min($secondsSmooth * 2, $smoothSeconds);
            }
        }
        if ($secondsSmooth < $totalDuration){
            $minValue = round(min($lastSmoothedValues), $precision);
            $deltaValue = round(($maxValue - $minValue) / (20 - count($result)), $precision);
            $nextValue = $lastSmoothedValue - $deltaValue;
            while ($nextValue >= $minValue){
                $longestDuration = self::longestDuration($nextValue, $lastSmoothedValues);
                if ($longestDuration > $secondsSmooth){
                    $result[] = [$longestDuration, $nextValue];
                }
                if ($nextValue === $minValue){
                    break;
                }
                $nextValue = max($nextValue - $deltaValue, $minValue);
            }
        }
        return json_encode($result);
    }
    public static function shrink($metrics, $shrinkSeconds, $smoothSeconds = 0, $precision = 0){
        $smoothSeconds = empty($smoothSeconds) ? $shrinkSeconds : $smoothSeconds;
        return json_encode(Utl::array_shrink
            (array_map(function($key, $value) use ($precision){return [$key + 1, round($value, $precision)];}, array_keys($metrics), $smoothSeconds === 1 ? $metrics : Average::exponentialMovingAverage($metrics, intval($smoothSeconds))),
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