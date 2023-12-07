<?php
namespace TukosLib\Objects\Sports\Strava\Activities;

use TukosLib\Objects\Sports\KpisFormulaes as KF;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

trait Kpis {

    public static $beta = 2.6, $refCadence = 180, $thresholdsMap = ['heartrate' => 'hrthreshold', 'power' => 'ftp', 'speed' => 'speedthreshold'], $minsMap = ['heartrate' => 'hrmin'],
        $functionsMap = ['avgload' => 'avgLoad', 'load' => 'load', 'timeabove' => 'timeAbove', 'timebelow' => 'timeBelow'],
        $streamsMap = ['heartrate' => 'heartrate', 'power' => 'watts', 'distance' => 'distance', 'cadence' => 'cadence', 'grade' => 'grade_smooth', 'speed' => 'velocity_smooth'],
        $kpisDescription = [
            'heartrate_avgload' => ['metrics' => 'avghr', 'activityParams' => ['secondsActive' => 'timemoving'], 'athleteParams' => ['threshold' => 'hrthreshold', 'sex' => 'sex', 'min' => 'hrmin']],
            'heartrate_load' => ['metrics' => 'heartratestream', 'athleteParams' => ['threshold' => 'hrthreshold', 'sex' => 'sex', 'min' => 'hrmin']],
            'power_avgload' => ['metrics' => 'avgpw', 'activityParams' => ['secondsActive' => 'timemoving'], 'athleteParams' => ['threshold' => 'ftp', 'sex' => 'sex']],
            'power_load' => ['metrics' => 'wattsstream', 'athleteParams' => ['threshold' => 'ftp', 'sex' => 'sex'], 'otherParams' => ['smoothSeconds' => 30]],
            'mechanical_load' => ['metrics' => 'velocity_smoothstream', 'activityParams' => ['cadencestream' => 'cadencestream'], 'athleteParams' => ['threshold' => 'speedthreshold'], 'otherParams' => ['b' => ['cadenceCorrection']]],
    ];
        public function zoneDescription($explodedName, $athlete){
            if ($explodedName[2] === 'threshold'){
                return ['metrics' => "$explodedName[0]stream", 'athleteParams' => ['threshold' => self::$thresholdsMap[$explodedName[0]]]];
            }else{
                
            }
        }
    
    public static function valueOf($name){
        if ($name[0] === 'n' && is_numeric($value = substr($name, 1))){
            return - $value;
        }else{
            return $name;
        }
    }
    public static function getThreshold($name, $index, $athlete){
        if ($name[$index] === 'threshold'){
            $threshold = ['heartrate' => $athlete['hrthreshold'], 'power' => $athlete['ftp'], 'speed' => $athlete['speedthreshold']][$name[0]];
            if (isset($name[index+1]) && is_numeric($name[$index+1])){
                $threshold = $threshold * $name[$index+1] / 100;
            }
            return $threshold;
        }else{
            return self::valueOf($name[$index]);
        }
    }
    public static function hasRequiredAthleteParams($kpiName, $athlete){
        $explodedName = explode('_', $kpiName);
        if (empty($explodedName[2])){
            return empty(array_diff(self::$kpisDescription[$kpiName]['athleteParams'], array_keys($athlete)));
        }else if ($explodedName[2] === 'threshold'){//heartrate_timeabove_threshold, heartrate_timeabove__threshold_105
            return !empty($athlete[self::$thresholdsMap[$explodedName[0]]]);
        }else{// heartrate_timeabove_142
            return true;
        }
    }
    public static function requiredActivityParams($kpiName){
        $explodedName = explode('_', $kpiName);
        if (empty($explodedName[2])){
            $activityParams = Utl::getItem('activityParams', self::$kpisDescription[$kpiName]);
            $metricsParams = Utl::getItem('metrics', self::$kpisDescription[$kpiName]);
            return $activityParams ? ($metricsParams ? array_merge(array_values($activityParams), [$metricsParams]) : array_values($activityParams)) : [$metricsParams];
        }else{// heartrate_timeabove_142
            return ["$explodedName[0]stream"];
        }
    }
    public static function hasRequiredActivityParams($kpiName, $activityColsToGet){
        return empty(array_diff(self::requiredActivityParams($kpiName), array_keys($activityColsToGet)));
    }
    public static function kpiDescription($kpiName, $athlete, $activityColsToGet){
        $explodedName = explode('_', $kpiName);
        $formula = self::$functionsMap[$explodedName[1]];
        if (empty($explodedName[2])){
            $description = self::$kpisDescription[$kpiName];
            $arguments = ['metrics' => $activityColsToGet[$description['metrics']]];
            if (!empty($description['athleteParams'])){
                $arguments = array_merge($arguments, array_combine(array_keys($description['athleteParams']), array_map(function($param) use ($athlete) {return $athlete[$param];}, $description['athleteParams'])));
            }
            if (!empty($description['activityParams'])){
                $arguments = array_merge($arguments, array_combine(array_keys($description['activityParams']), array_map(function($param) use ($activityColsToGet) {return $activityColsToGet[$param];}, $description['activityParams'])));
            }
            if (!empty($description['otherParams'])){
                $arguments = array_merge($arguments, $description['otherParams']);
            }
        }else{
            $arguments = ['metrics' => $activityColsToGet[self::$streamsMap[$explodedName[0]] . 'stream'], 'uncertainty' => 0.02, 'fuzzyType' => 'relative'];
            if($explodedName[2] === 'threshold'){
                $thresholdRatio = empty($explodedName[3]) ? 100 : $explodedName[3];
                $thresholdValue = intval($athlete[self::$thresholdsMap[$explodedName[0]]]);
                $minValue = empty($minIndex = Utl::getItem($explodedName[0], self::$minsMap)) ? 0 : $athlete[$minIndex];
                $arguments['threshold'] = $thresholdValue * $thresholdRatio / 100;
                $arguments['threshold'] = intval(($thresholdValue - $minValue) * $thresholdRatio / 100 + $minValue);
            }else{
                $arguments['threshold'] = self::valueOf($explodedName[2]);
            }
        }
        return ['formula' => $formula, 'arguments' => $arguments];
    }
    public function computeKpis($athleteId, $activitiesKpisToGet, $activities = [], $missingKpisIndex = 'id'){
        $kpis = []; $kpisOKtoCompute = [];
        $athlete = array_filter(Tfk::$registry->get('objectsStore')->objectModel('sptathletes')->getOne(['where' => ['id' => $athleteId], 'cols' => ['hrmin', 'hrthreshold', 'ftp', 'speedthreshold', 'sex']]));
        $activities = array_filter($activities);
        $existingActivitiesKpis = Utl::toAssociative($this->getall(['where' => [['col' => $missingKpisIndex, 'opr' => 'in', 'values' => array_keys($activitiesKpisToGet)]], 'cols' => [$missingKpisIndex, 'kpiscache']]), $missingKpisIndex);
        foreach($activitiesKpisToGet as $activityId => $activityKpisToGet){
            $kpisToCompute = []; $activityColsToGet = []; $existingActivityKpis = $existingActivitiesKpis[$activityId]; $flippedKpisToGet = array_flip($activityKpisToGet); $kpis[$activityId] = [];
            $missingActivityKpisToGet = array_flip(array_diff_key($flippedKpisToGet, $existingActivityKpis));
            foreach($missingActivityKpisToGet as $kpiName){
                if (self::hasRequiredAthleteParams($kpiName, $athlete)){
                    $kpisToCompute[] = $kpiName;
                    $activityColsToGet = array_merge($activityColsToGet, self::requiredActivityParams($kpiName)); 
                }
            }
            if (!empty($activityColsToGet)){
                $activityColsToGet = array_unique($activityColsToGet);
                $activity = ($activity = Utl::getItem($activityId, $activities)) ? $activity : array_filter($this->getOne(['where' => [$missingKpisIndex => $activityId], 'cols' => $activityColsToGet]));
                foreach($kpisToCompute as $kpiName){
                    if (self::hasRequiredActivityParams($kpiName, $activity)){
                        $kpisOKtoCompute[] = $kpiName;
                    }
                }
                foreach($activity as $col => &$param){
                    if (substr($col, -6) === 'stream' && is_string($param)){
                        $param = json_decode($param);
                    }
                }
                foreach ($kpisOKtoCompute as $kpiName){
                    $description = self::kpiDescription($kpiName, $athlete, $activity);
                    if (Utl::getItem('b', $description['arguments']) === 'cadenceCorrection'){
                        if (!empty($activity['cadencestream'])){
                            $correction = self::$beta * self::$refCadence;
                            foreach($activity['cadencestream'] as $cadence){
                                $b[] = $correction / ($cadence > 50 ? $cadence : 50);
                            }
                            $description['arguments']['b'] = $b;
                        }else{
                            $description['arguments']['b'] = self::$beta;
                        }
                    }
                    $funcName = $description['formula'];
                    $kpis[$activityId][$kpiName] = KF::$funcName(...$description['arguments']);
                }
                $this->updateOne(newValues: [$missingKpisIndex => $activityId, 'kpiscache' => Utl::getItem($activityId, $kpis, 0, 0)], atts: ['where' => [$missingKpisIndex => $activityId]], jsonFilter: true);
            }
            $kpis[$activityId] = array_merge($kpis[$activityId], array_intersect_key($existingActivityKpis, $flippedKpisToGet));
        }
        return $kpis;
    }
    public function getKpis($query, $activitiesKpisToGet){// associated to process action
        return ['data' => ['kpis' => $this->computeKpis($query['athlete'], $activitiesKpisToGet)]];
    }
}
?>