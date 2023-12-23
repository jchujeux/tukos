<?php
namespace TukosLib\Objects\Sports\Strava\Activities;

use TukosLib\Objects\Sports\KpisFormulaes as KF;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

trait Kpis {

    public static $beta = 2.6, $refCadence = 180, $thresholdsMap = ['heartrate' => 'hrthreshold', 'power' => 'ftp', 'speed' => 'speedthreshold'], $minsMap = ['heartrate' => 'hrmin'],
        $functionsMap = ['avgload' => 'avgLoad', 'load' => 'load', 'timeabove' => 'timeAbove', 'timebelow' => 'timeBelow', 'timecurve' => 'timeCurve', 'durationcurve' => 'durationCurve', 'shrink' => 'shrink'],
        $streamsMap = ['heartrate' => 'heartrate', 'power' => 'watts', 'distance' => 'distance', 'cadence' => 'cadence', 'grade' => 'grade_smooth', 'speed' => 'velocity_smooth'],
        $kpisDescription = [
            'heartrate_avgload' => ['metrics' => 'avghr', 'activityParams' => ['secondsActive' => 'timemoving'], 'athleteParams' => ['threshold' => 'hrthreshold', 'sex' => 'sex', 'min' => 'hrmin']],
            'heartrate_load' => ['metrics' => 'stream', 'athleteParams' => ['threshold' => 'hrthreshold', 'sex' => 'sex', 'min' => 'hrmin']],
            'power_avgload' => ['metrics' => 'avgpw', 'activityParams' => ['secondsActive' => 'timemoving'], 'athleteParams' => ['threshold' => 'ftp', 'sex' => 'sex']],
            'power_load' => ['metrics' => 'stream', 'athleteParams' => ['threshold' => 'ftp', 'sex' => 'sex'], 'otherParams' => ['smoothSeconds' => 30]],
            'mechanical_load' => ['metrics' => 'velocity_smoothstream', 'activityParams' => ['cadencestream' => 'cadencestream'], 'athleteParams' => ['threshold' => 'speedthreshold'], 'otherParams' => ['b' => ['cadenceCorrection']]],
            'timecurve' => ['metrics' => 'stream', 'athleteParams' => []],
            'durationcurve' => ['metrics' => 'stream', 'athleteParams' => []],
            'timeabove' => ['metrics' => 'stream', 'otherParams' => ['uncertainty' => 0.02, 'fuzzyType' => 'relative']],
            'timebelow' => ['metrics' => 'stream', 'otherParams' => ['uncertainty' => 0.02, 'fuzzyType' => 'relative']],
            'shrink' => ['metrics' => 'stream', 'otherParams' => ['intval' => true]],
        ];
    public static function getDescription($name, $formula){
        $description = isset(self::$kpisDescription[$name.'_'.$formula]) ? self::$kpisDescription[$name.'_'.$formula] : Utl::getItem($formula, self::$kpisDescription, []);
        if (Utl::getItem('metrics', $description) === 'stream'){
            $description['metrics'] = self::$streamsMap[$name].'stream';
        }
        return $description;
    }
    /*public static function zoneDescription($explodedName, $athlete){
        if ($explodedName[2] === 'threshold'){
            return ['metrics' => "$explodedName[0]stream", 'athleteParams' => ['threshold' => self::$thresholdsMap[$explodedName[0]]]];
        }else{
            
        }
    }*/
    public static function valueOf($name){
        if ($name[0] === 'n' && is_numeric($value = substr($name, 1))){
            return - $value;
        }else{
            return $name;
        }
    }
    /*public static function getThreshold($name, $index, $athlete){
        if ($name[$index] === 'threshold'){
            $threshold = ['heartrate' => $athlete['hrthreshold'], 'power' => $athlete['ftp'], 'speed' => $athlete['speedthreshold']][$name[0]];
            if (isset($name[index+1]) && is_numeric($name[$index+1])){
                $threshold = $threshold * $name[$index+1] / 100;
            }
            return $threshold;
        }else{
            return self::valueOf($name[$index]);
        }
    }*/
    public static function hasRequiredAthleteParams($kpiName, $athlete){
        [$name, $formula, $param1] = array_pad(explode('_', $kpiName), 3, '');
        return empty(array_diff(Utl::getItem('athleteParams', self::getDescription($name, $formula), []), array_keys($athlete))) && $param1 === 'threshold' ? !empty($athlete[self::$thresholdsMap[$name]]) : true;
    }
    public static function requiredActivityParams($kpiName){
        [$name, $formula, $param1] = array_pad(explode('_', $kpiName), 3, '');
        ['activityParams' => $activityParams, 'metrics' => $metrics] = Utl::getItems(['activityParams', 'metrics'], self::getDescription($name, $formula), false, []);
        return $activityParams ? ($metrics ? array_merge(array_values($activityParams), [$metrics]) : array_values($activityParams)) : ($metrics ? [$metrics] : []);
    }
    public static function hasRequiredActivityParams($kpiName, $activityColsToGet){
        return empty(array_diff(self::requiredActivityParams($kpiName), array_keys($activityColsToGet)));
    }
    public static function kpiFunctionAndArguments($kpiName, $athlete, $activity){
        [$name, $formula, $param1, $param2] = array_pad(explode('_', $kpiName), 4, '');
        $description = self::getDescription($name, $formula);
        $arguments = ['metrics' => $activity[$description['metrics']]];
        if (!empty($description['athleteParams'])){
            $arguments = array_merge($arguments, array_combine(array_keys($description['athleteParams']), array_map(function($param) use ($athlete) {return $athlete[$param];}, $description['athleteParams'])));
        }
        if (!empty($description['activityParams'])){
            $arguments = array_merge($arguments, array_combine(array_keys($description['activityParams']), array_map(function($param) use ($activity) {return $activity[$param];}, $description['activityParams'])));
        }
        if (!empty($description['otherParams'])){
            $arguments = array_merge($arguments, $description['otherParams']);
        }
        switch($formula){
            case 'timeabove':
            case 'timebelow':
                if ($param1 === 'threshold'){
                    $thresholdRatio = empty($param2) ? 100 : $param2;
                    $thresholdValue = intval($athlete[self::$thresholdsMap[$name]]);
                    $minValue = empty($minIndex = Utl::getItem($name, self::$minsMap)) ? 0 : $athlete[$minIndex];
                    $arguments['threshold'] = $thresholdValue * $thresholdRatio / 100;
                    $arguments['threshold'] = intval(($thresholdValue - $minValue) * $thresholdRatio / 100 + $minValue);
                }else{
                    $arguments['threshold'] = self::valueOf($param1);
                }
                break;
            case 'shrink':
                $arguments['shrinkSeconds'] = $param1;
                $arguments['smoothSeconds'] = $param2;
        }
        return [self::$functionsMap[$formula], $arguments];
    }
    public function computeKpis($athleteId, $activitiesKpisToGet, $activities = [], $missingKpisIndex = 'id'){
        $kpis = []; $kpisOKtoCompute = [];
        $athlete = array_filter(Tfk::$registry->get('objectsStore')->objectModel('sptathletes')->getOne(['where' => ['id' => $athleteId], 'cols' => ['hrmin', 'hrthreshold', 'ftp', 'speedthreshold', 'sex']]));
        $activities = array_filter($activities);
        $existingActivitiesKpis = Utl::toAssociative($this->getAllExtended(['where' => [['col' => $missingKpisIndex, 'opr' => 'in', 'values' => array_keys($activitiesKpisToGet)]], 'cols' => [$missingKpisIndex, 'kpiscache']]), $missingKpisIndex);
        foreach($activitiesKpisToGet as $activityId => $activityKpisToGet){
            $kpisToCompute = []; $activityColsToGet = []; $existingActivityKpis = $existingActivitiesKpis[$activityId]; $flippedKpisToGet = array_flip($activityKpisToGet); $kpis[$activityId] = [];
            $missingActivityKpisToGet = array_flip(array_diff_key($flippedKpisToGet, $existingActivityKpis));
            foreach($missingActivityKpisToGet as $kpiName){
                if (self::hasRequiredAthleteParams($kpiName, $athlete)){
                    $kpisToCompute[] = $kpiName;
                    $activityColsToGet = array_merge($activityColsToGet, self::requiredActivityParams($kpiName)); 
                }else{
                    $kpis[$activityId][$kpiName] = false;
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
                    [$funcName, $arguments] = self::kpiFunctionAndArguments($kpiName, $athlete, $activity);
                    if (Utl::getItem('b', $arguments) === 'cadenceCorrection'){
                        if (!empty($activity['cadencestream'])){
                            $correction = self::$beta * self::$refCadence;
                            foreach($activity['cadencestream'] as $cadence){
                                $b[] = $correction / ($cadence > 50 ? $cadence : 50);
                            }
                            $arguments['b'] = $b;
                        }else{
                            $arguments['b'] = self::$beta;
                        }
                    }
                    $kpis[$activityId][$kpiName] = KF::$funcName(...$arguments);
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