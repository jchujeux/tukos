<?php
namespace TukosLib\Objects\Sports;

use MathPHP\Statistics\Average;
use TukosLib\Utils\Fuzzy as FZ;
//use TukosLib\Utils\Utilities as Utl;

class TrainingFormulaes {
    
    private static $b = ['male' => 1.92, 'female' => 1.64], $hrThresholdToMaxRatio = 0.8, $bHr = [], $beta = 2.6, $refCadence = 90;
    
    public static function init(){
        foreach(self::$b as $sex => $bValue){
            self::$bHr[$sex] = $bValue * self::$hrThresholdToMaxRatio; // needed to truly match Banister b value as below we use the the hrThresoldRatio (1 at hrThreshold), when Banister uses the hrMaxRatio (1 at hrMax)
        }
    }
    public static function intensity($rate, $b){
        return $rate * exp($b * ($rate - 1));
    }
/*    public static function hrRatio($hr, $hrMin, $hrMax){
        return ($hr - $hrMin) / ($hrMax - $hrMin);
    }*/
    public static function hrThresholdRatio($hr, $hrMin, $hrThreshold){
        return ($hr - $hrMin) / ($hrThreshold - $hrMin);
    }
    public static function hrIntensity($hr, $hrMin, $hrThreshold, $sex){
        return ($hr <= $hrMin) ? 0 : self::intensity(self::hrThresholdRatio($hr, $hrMin, $hrThreshold), self::$bHr[$sex]);
    }
    public static function pwIntensity($pw, $pwThreshold, $sex){
        return self::intensity($pw / $pwThreshold, self::$b[$sex]); //should we use the same ratio for power as for heart rate ?
    }
    public static function avgHrTrainingload($avgHr, $hrMin, $hrThreshold, $minutesActive, $sex){
        return intval($minutesActive / 0.6 * self::hrIntensity($avgHr, $hrMin, $hrThreshold, $sex));
    }
    public static function avgPwTrainingload($avgPw, $pwThreshold, $minutesActive, $sex){
        return intval($minutesActive / 0.6 * self::pwIntensity($avgPw, $pwThreshold, $sex));
    }
    public static function hrTrainingLoad($hrs, $hrMin, $hrThreshold, $sex){//$hrs is an array of hr values for every second of the workout
        $load = 0;
        $distinctHrs= array_count_values($hrs);
        foreach ($distinctHrs as $hr => $secondsActive){
            $load += $secondsActive * self::hrIntensity($hr, $hrMin, $hrThreshold, $sex);
        }
        return intval($load / 36);
    }
    public static function pwTrainingLoad($pws, $pwThreshold, $sex, $N_seconds = 30){//$pws is an array of power values for every second of the workout
        if ($N_seconds != 1){
            $pws = array_map(function($value){return intval($value);}, Average::exponentialMovingAverage($pws, $N_seconds));
        }else if (empty($pws[0])){
            $pws[0] = 0;
        }
        $load = 0;
        $distinctPws= array_count_values($pws);
        foreach ($distinctPws as $pw => $secondsActive){
            $load += $secondsActive * self::pwIntensity($pw, $pwThreshold, $sex);
        }
        return intval($load / 36);
    }
    public static function runningMechanicalIntensity($speed, $cadence, $thresholdSpeed){
        $rate = $speed / $thresholdSpeed;
        return $rate * exp(self::$beta * ($rate -1) * self::$refCadence / $cadence);
    }
    public static function avgRunningMechanicalLoad($avgSpeed, $avgCadence, $thresholdSpeed, $minutesActive){
        return self::mechanicalIntensity($avgSpeed, $avgCadence, $thresholdSpeed, $minutesActive);
    }
    public static function runningMechanicalLoad(/*meters*/$distances, $cadences, /*meters / secoonds*/$thresholdSpeed){
        $lastIndex = count($distances) - 1;
        $load = 0;
        for ($i = 1; $i <= $lastIndex; $i++){
            if ($cadences[$i] > 10){
                $load += self::runningMechanicalIntensity($distances[$i] - $distances[$i-1], $cadences[$i], $thresholdSpeed);
            }
        }
        return intval($load);
    }
    public static function timeInZones($values, $thresholds, $uncertainty = 3){
        $timeInZones = [];
        for ($i = 0; $i <= count($thresholds); $i++){
            $timeInZones[$i] = 0;
        }
        $fuzzyDomain = FZ::absoluteFuzzyDomain($thresholds, $uncertainty);
        $distinctValues = array_count_values($values);
        foreach ($distinctValues as $value => $secondsActive){
            $fuzzyValue = FZ::absoluteFuzzyValue($value, $fuzzyDomain);
            foreach($fuzzyValue as $key => $value){
                $timeInZones[$key] += $secondsActive * $value;
            }
        }
        foreach ($timeInZones as $key => &$value){
            $value = intval($value);
        }
        return $timeInZones;
    }
}
TrainingFormulaes::init();
?>