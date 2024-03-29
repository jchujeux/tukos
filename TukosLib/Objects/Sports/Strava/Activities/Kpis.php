<?php
namespace TukosLib\Objects\Sports\Strava\Activities;

use TukosLib\Objects\Sports\KpisFormulaes as KF;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

trait Kpis {

    public static $beta = 2.6, $refCadence = 180, $thresholdsMap = ['heartrate' => 'hrthreshold', 'power' => 'ftp', 'speed' => 'speedthreshold'], $minsMap = ['heartrate' => 'hrmin'],
        $functionsMap = ['avgload' => 'avgLoad', 'load' => 'load', 'timeinzones' => 'timeInZones', 'timeabove' => 'timeAbove', 'timebelow' => 'timeBelow', 'loadinzones' => 'loadInZones', 'loadabove' => 'loadAbove', 'loadbelow' => 'loadBelow', 
            'timecurve' => 'timeCurve', 'durationcurve' => 'durationCurve', 'shrink' => 'shrink'],
        $streamsMap = ['heartrate' => 'heartrate', 'power' => 'watts', 'distance' => 'distance', 'cadence' => 'cadence', 'grade' => 'grade_smooth', 'speed' => 'velocity_smooth'],
        $athleteParamsDescription = ['heartrate' => ['threshold' => 'hrthreshold', 'sex' => 'sex', 'min' => 'hrmin'], 'power' => ['threshold' => 'ftp', 'sex' => 'sex'], 'mechanical' => ['threshold' => 'speedthreshold']],
        $kpisDescription = [
            'heartrate_avgload' => ['metrics' => 'avghr', 'activityParams' => ['secondsActive' => 'timemoving']],
            'power_avgload' => ['metrics' => 'avgpw', 'activityParams' => ['secondsActive' => 'timemoving']],
            'load' => ['metrics' => 'stream'],
            'mechanical_load' => ['metrics' => 'velocity_smoothstream', 'activityParams' => ['cadencestream' => 'cadencestream'], 'otherParams' => ['b' => ['cadenceCorrection']]],
            'timecurve' => ['metrics' => 'stream'],
            'durationcurve' => ['metrics' => 'stream'],
            'timeinzones' => ['metrics' => 'stream', 'otherParams' => ['fuzzyType' => 'absolute']],
            'timeabove' => ['metrics' => 'stream', 'otherParams' => ['fuzzyType' => 'absolute']],
            'timebelow' => ['metrics' => 'stream', 'otherParams' => ['fuzzyType' => 'absolute']],
            'loadinzones' => ['metrics' => 'stream', 'otherParams' => ['fuzzyType' => 'absolute']],
            'loadabove' => ['metrics' => 'stream', 'otherParams' => ['fuzzyType' => 'absolute']],
            'loadbelow' => ['metrics' => 'stream', 'otherParams' => ['fuzzyType' => 'absolute']],
            'shrink' => ['metrics' => 'stream', 'otherParams' => ['intval' => true]],
            'heartrate' => ['otherParams' => ['uncertainty' => 3]],
            'power' => ['otherParams' => ['uncertainty' => 10]],
        ];
    public static function getDescription($name, $formula){
        $description = isset(self::$kpisDescription[$name.'_'.$formula]) ? self::$kpisDescription[$name.'_'.$formula] : Utl::getItem($formula, self::$kpisDescription, []);
        if (Utl::getItem('metrics', $description) === 'stream'){
            $description['metrics'] = self::$streamsMap[$name].'stream';
        }
        if (strpos($formula, 'time') === false  && strpos($formula, 'duration') === false&& strpos($formula, 'shrink') === false){
            $description['athleteParams'] = Utl::getItem($name, self::$athleteParamsDescription, []);
        }
        if (isset($description['otherParams']) && isset(self::$kpiDescriptions[$name])){
            $description['otherParams'] = array_merge($description['otherParams'], Utl::getItem('otherParams', self::$kpisDescription[$name], []));
        }
        if ($name === 'power' && strpos($formula, 'load') !== false && strpos($formula, 'avg') === false){
            $description['otherParams'] = isset($description['otherParams']) ? array_merge($description['otherParams'], ['smoothSeconds' => 30]) : ['smoothSeconds' => 30];
        }
        return $description;
    }
    public static function valueOf($name){
        if ($name[0] === 'n' && is_numeric($value = substr($name, 1))){
            return - $value;
        }else{
            return $name;
        }
    }
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
            case 'loadabove':
            case 'loadbelow':
                if ($param1 === 'threshold'){
                    $thresholdRatio = empty($param2) ? 100 : $param2;
                    $thresholdValue = intval($athlete[self::$thresholdsMap[$name]]);
                    $minValue = empty($minIndex = Utl::getItem($name, self::$minsMap)) ? 0 : $athlete[$minIndex];
                    $arguments['kpiThreshold'] = $thresholdValue * $thresholdRatio / 100;
                    $arguments['kpiThreshold'] = intval(($thresholdValue - $minValue) * $thresholdRatio / 100 + $minValue);
                }else{
                    $arguments['kpiThreshold'] = self::valueOf($param1);
                }
                break;
            case 'timeinzones':
            case 'loadinzones':
                $minValue = empty($minIndex = Utl::getItem($name, self::$minsMap)) ? 0 : $athlete[$minIndex];
                if ($param1 === 'threshold'){
                    $thresholdValue = intval($athlete[self::$thresholdsMap[$name]]);
                    $kpiThresholds = array_slice(explode('_', $kpiName), 3);
                    foreach($kpiThresholds as &$threshold){
                        $threshold = intval(($thresholdValue - $minValue) * $threshold / 100 + $minValue);
                    }
                    $arguments['kpiThresholds'] = $kpiThresholds;
                }else{
                    $arguments['kpiThresholds'] = array_slice(explode('_', $kpiName), 2);
                }
                break;
            case 'shrink':
                $arguments['shrinkSeconds'] = $param1;
                $arguments['smoothSeconds'] = $param2;
                break;
            case 'timecurve':
            case 'durationcurve':
                $arguments['smoothSeconds'] = $param1 ? $param1 : 1;
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