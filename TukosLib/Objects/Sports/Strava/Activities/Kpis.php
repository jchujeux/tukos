<?php
namespace TukosLib\Objects\Sports\Strava\Activities;

use TukosLib\Objects\Sports\KpisFormulaes as KF;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

trait Kpis {

    public static $beta = 2.6, $refCadence = 180, $thresholdsMap = ['heartrate' => 'hrthreshold', 'power' => 'ftp', 'speed' => 'speedthreshold'], $minsMap = ['heartrate' => 'hrmin'],
        $functionsMap = ['avgload' => 'avgLoad', 'load' => 'load', 'timeinzones' => 'timeInZones', 'timeabove' => 'timeAbove', 'timebelow' => 'timeBelow', 'loadinzones' => 'loadInZones', 'loadabove' => 'loadAbove', 'loadbelow' => 'loadBelow', 
            'timecurve' => 'timeCurve', 'durationcurve' => 'durationCurve', 'shrink' => 'shrink'],
        $streamsMap = ['heartrate' => 'heartrate', 'power' => 'watts', 'distance' => 'distance', 'cadence' => 'cadence', 'slope' => 'grade_smooth', 'speed' => 'velocity_smooth'],
        $metricsPrecision = ['heartrate' => 0, 'power' => 0, 'distance' => 1, 'cadence' => 0, 'slope' => 1, 'speed' => 2],
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
            'shrink' => ['metrics' => 'stream'],
            'heartrate' => ['otherParams' => ['uncertainty' => 3]],
            'power' => ['otherParams' => ['uncertainty' => 10]],
            'slope' => ['otherParams' => ['uncertainty' => 0.1]],
        ];
    public static function metricStream($name){
        return self::$streamsMap[$name] . 'stream';
    }
    public static function isMetricStream($name){
        return isset(self::$streamsMap[$name]);
    }
    public static function getDescription($name, $formula, $param1 = ''){
        $description = isset(self::$kpisDescription[$name.'_'.$formula]) ? self::$kpisDescription[$name.'_'.$formula] : Utl::getItem($formula, self::$kpisDescription, []);
        if (Utl::getItem('metrics', $description) === 'stream'){
            $description['metrics'] = self::metricStream($name);
        }
        if (strpos($formula, 'time') === false  && strpos($formula, 'duration') === false&& strpos($formula, 'shrink') === false){
            $description['athleteParams'] = Utl::getItem(self::isMetricStream($param1) ? $param1 : $name, self::$athleteParamsDescription, []);
        }
        if (Utl::drillDown($description, ['otherParams', 'fuzzyType']) && isset(self::$kpisDescription[$name])){
            //$description['otherParams'] = array_merge($description['otherParams'], Utl::getItem('otherParams', self::$kpisDescription[$name], []));
            $description = Utl::array_merge_recursive_replace($description, self::$kpisDescription[$name]);
        }
        if (($name === 'power' || $param1 === 'power')  && strpos($formula, 'avg') === false){
            $description['otherParams'] = isset($description['otherParams']) ? array_merge($description['otherParams'], ['smoothSeconds' => 30]) : ['smoothSeconds' => 30];
        }
        $description['precision'] = self::$metricsPrecision[$name];
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
        [$name, $formula, $param1, $param2] = array_pad(explode('_', $kpiName), 4, '');
        return empty(array_diff(Utl::getItem('athleteParams', self::getDescription($name, $formula, $param1), []), array_keys($athlete))) && ($param1 === 'threshold' || $param2 === 'threshold')? !empty($athlete[self::$thresholdsMap[$name]]) : true;
    }
    public static function requiredActivityParams($kpiName){
        [$name, $formula, $param1] = array_pad(explode('_', $kpiName), 3, '');
        ['activityParams' => $activityParams, 'metrics' => $metrics] = Utl::getItems(['activityParams', 'metrics'], self::getDescription($name, $formula), false, []);
        $metricsArray = $metrics ? (self::isMetricStream($param1) ? [$metrics, self::metricStream($param1)] : [$metrics]) : [];
        return $activityParams ? array_merge(array_values($activityParams), $metricsArray) : $metricsArray;
    }
    public static function hasRequiredActivityParams($kpiName, $activityColsToGet){
        return empty(array_diff(self::requiredActivityParams($kpiName), array_keys($activityColsToGet)));
    }
    public static function kpiFunctionAndArguments($kpiName, $athlete, $activity){
        [$name, $formula, $param1, $param2, $param3] = array_pad(explode('_', $kpiName), 5, '');
        $description = self::getDescription($name, $formula, $param1);
        $arguments = ['metrics' => $activity[$description['metrics']], 'precision' => $description['precision']];
        if (!empty($description['athleteParams'])){
            $arguments = array_merge($arguments, array_combine(array_keys($description['athleteParams']), array_map(function($param) use ($athlete) {return $athlete[$param];}, $description['athleteParams'])));
        }
        if (!empty($description['activityParams'])){
            $arguments = array_merge($arguments, array_combine(array_keys($description['activityParams']), array_map(function($param) use ($activity) {return $activity[$param];}, $description['activityParams'])));
        }
        if (!empty($description['otherParams'])){
            $arguments = array_merge($arguments, $description['otherParams']);
        }
        if (self::isMetricStream($param1)){
            $thresholdParam = 'param2';
            $nextParam = 'param3';
            $sliceValue = 4;
            $arguments['loadMetrics'] = $activity[self::metricStream($param1)];
            $arguments['precision'] = self::$metricsPrecision[$param1];
        }else{
            $thresholdParam = 'param1';
            $nextParam = 'param2';
            $sliceValue = 3;
            if (in_array($formula, ['loadabove', 'loadbelow', 'loadinzones'])){
                $arguments['loadMetrics'] = $arguments['metrics'];
            }
        }
        switch($formula){
            case 'timeabove':
            case 'timebelow':
            case 'loadabove':
            case 'loadbelow':
                if ($$thresholdParam === 'threshold'){
                    $thresholdRatio = empty($$nextParam) ? 100 : $$nextParam;
                    $thresholdValue = round($athlete[self::$thresholdsMap[$name]], $description['precision']);
                    $minValue = empty($minIndex = Utl::getItem($name, self::$minsMap)) ? 0 : $athlete[$minIndex];
                    $arguments['kpiThreshold'] = $thresholdValue * $thresholdRatio / 100;
                    $arguments['kpiThreshold'] = round(($thresholdValue - $minValue) * $thresholdRatio / 100 + $minValue, $description['precision']);
                }else{
                    $arguments['kpiThreshold'] = self::valueOf($param1);
                }
                break;
            case 'timeinzones':
            case 'loadinzones':
                if ($$thresholdParam === 'threshold'){
                    $minValue = empty($minIndex = Utl::getItem($name, self::$minsMap)) ? 0 : $athlete[$minIndex];
                    $thresholdValue = round($athlete[self::$thresholdsMap[$name]], $description['precision']);
                    $kpiThresholds = array_slice(explode('_', $kpiName), $sliceValue);
                    foreach($kpiThresholds as &$threshold){
                        $threshold = round(($thresholdValue - $minValue) * $threshold / 100 + $minValue, $description['precision']);
                    }
                    $arguments['kpiThresholds'] = $kpiThresholds;
                }else{
                    $arguments['kpiThresholds'] = array_slice(explode('_', $kpiName), $sliceValue -1);
                }
                break;
            case 'shrink':
                $arguments['shrinkSeconds'] = $param1;
                $arguments['smoothSeconds'] = $param2;
                break;
            case 'timecurve':
            case 'durationcurve':
            case 'load':
                $arguments['smoothSeconds'] = $param1 ? $param1 : 1;
        }
        return [self::$functionsMap[$formula], $arguments];
    }
    public function computeKpis($athleteId, $activitiesKpisToGet, $missingKpisIndex = 'id'){
        $kpis = [];
        $athlete = array_filter(Tfk::$registry->get('objectsStore')->objectModel('sptathletes')->getOne(['where' => ['id' => $athleteId], 'cols' => ['hrmin', 'hrthreshold', 'ftp', 'speedthreshold', 'sex']]));
        $stravaActivitiesModel = Tfk::$registry->get('objectsStore')->objectModel('stravaactivities');
        foreach($activitiesKpisToGet as $activityId => $activityKpisToGet){
            $kpisToCompute = [];  $kpisOKtoCompute = []; $activityColsToGet = []; 
            foreach($activityKpisToGet as $kpiName){
                if (self::hasRequiredAthleteParams($kpiName, $athlete)){
                    $kpisToCompute[] = $kpiName;
                    $activityColsToGet = array_merge($activityColsToGet, self::requiredActivityParams($kpiName)); 
                }else{
                    $kpis[$activityId][$kpiName] = false;
                }
            }
            if (!empty($activityColsToGet)){
                $activityColsToGet = array_unique($activityColsToGet);
                $activity = array_filter($stravaActivitiesModel->getOne(['where' => [$missingKpisIndex => $activityId], 'cols' => $activityColsToGet]));
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
                $kpisWhichCouldNotBeComputed = array_diff($kpisToCompute, $kpisOKtoCompute);
                foreach($kpisWhichCouldNotBeComputed as $kpiName){
                    $kpis[$activityId][$kpiName] = 0;
                }
            }
        }
        return $kpis;
    }
    public function getKpis($query, $activitiesKpisToGet){// associated to process action
        return ['data' => ['kpis' => $this->computeKpis($query['athleteid'], $activitiesKpisToGet)]];
    }
}
?>