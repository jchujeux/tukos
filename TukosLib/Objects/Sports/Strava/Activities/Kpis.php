<?php
namespace TukosLib\Objects\Sports\Strava\Activities;

use TukosLib\Objects\Sports\KpisFormulaes as KF;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

trait Kpis {

    public static $beta = 2.6, $refCadence = 180/*, $thresholdsMap = ['heartrate' => 'hrthreshold', 'power' => 'ftp', 'speed' => 'speedthreshold']*/, $minsMap = ['heartrate' => 'hrmin'],
        $functionsMap = ['avgload' => 'avgLoad', 'avg' => 'estimatedpower_avg', 'estimatedavg' => 'power_estimatedavg', 'rawwattsstream' => 'estimatedRawWattsStream', 'load' => 'load', 'timeinzones' => 'timeInZones',
            'timeabove' => 'timeAbove', 'timebelow' => 'timeBelow', 'loadinzones' => 'loadInZones', 'loadabove' => 'loadAbove', 'loadbelow' => 'loadBelow', 'timecurve' => 'timeCurve', 'durationcurve' => 'durationCurve', 'shrink' => 'shrink'],
        $streamsMap = ['heartrate' => 'heartrate', 'power' => 'watts'/*, 'powercalcstream' => 'watts_calc'*/, 'distance' => 'distance', 'cadence' => 'cadence'/*, 'slope' => 'grade_smooth', 'speed' => 'velocity_smooth'*/,
            'estimatedpowerstream' => 'estimatedpower_watts', 'estimatedrawpowerstream' => 'estimatedpower_rawwatts'],
        $metricsPrecision = ['heartrate' => 0, 'power' => 0, 'watts' => 0, 'estimatedpower' => 0/*, 'estimatedpowerstream' => 0*/, 'estimatedrawpowerstream' => 0, 'distance' => 1, 'elevationgain' => 0,  'cadence' => 0, 'slope' => 1, 
            'speed' => 2, 'powercalcstream' => 0],
        $athleteParamsDescription = ['heartrate' => ['threshold' => 'hrthreshold', 'sex' => 'sex', 'min' => 'hrmin'], 'power' => ['threshold' => 'ftp', 'sex' => 'sex'], 'estimatedpowerstream' => ['threshold' => 'ftp', 'sex' => 'sex'],
            'estimatedrawpowerstream' => ['threshold' => 'ftp', 'sex' => 'sex'], 'powercalcstream' => ['threshold' => 'ftp', 'sex' => 'sex'], 'mechanical' => ['threshold' => 'speedthreshold'], 'estimatedpower' => ['weight' => 'weight', 'ftp' => 'ftp']
        ],
        $namesMap = [/*'estimatedpowerstream' => 'estimatedpower_wattsstream', */'estimatedrawpowerstream' => 'estimatedpower_rawwattsstream'/*, 'powercalcstream' => 'watts_calcstream'*/],
        $kpisDescription = [
            'estimatedpower_avg' => ['metrics' => 'distance', 'activityParams' => ['secondsActive' => 'timemoving', 'elevationGain' => 'elevationgain'], 
                'optionalActivityParams' => ['extraWeight' => 'extraweight', 'frictionCoef' => 'frictioncoef', 'dragCoef' => 'dragcoef', 'windVelocity' => 'windvelocity']],
            'heartrate_avgload' => ['metrics' => 'avghr', 'activityParams' => ['secondsActive' => 'timemoving']],
            'power_avgload' => ['metrics' => 'avgpw', 'activityParams' => ['secondsActive' => 'timemoving']],
            'estimatedpower_wattsstream' => ['metrics' => 'velocity_smoothstream', 'activityParams' => ['grade_smoothstream' => 'grade_smoothstream'],
                'optionalActivityParams' => ['extraWeight' => 'extraweight', 'frictionCoef' => 'frictioncoef', 'dragCoef' => 'dragcoef', 'windVelocity' => 'windvelocity']],
            'estimatedpower_rawwattsstream' => ['metrics' => 'distancestream', 'activityParams' => ['altitudestream' => 'altitudestream', 'latitudestream' => 'latitudestream', 'longitudestream' => 'longitudestream'],
                'optionalActivityParams' => ['extraWeight' => 'extraweight', 'frictionCoef' => 'frictioncoef', 'dragCoef' => 'dragcoef', 'windVelocity' => 'windvelocity', 'windDirection' => 'winddirection']],
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
            'power' => ['otherParams' => ['uncertainty' => 6]],
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
        if (strpos($formula, 'time') === false  && strpos($formula, 'duration') === false && strpos($formula, 'shrink') === false && strpos($formula, 'estimatedavg') === false){
            $description['athleteParams'] = Utl::getItem(self::isMetricStream($param1) ? $param1 : $name, self::$athleteParamsDescription, []);
        }
        if (Utl::drillDown($description, ['otherParams', 'fuzzyType']) && isset(self::$kpisDescription[$name])){
            $description = Utl::array_merge_recursive_replace($description, self::$kpisDescription[$name]);
        }
        if ((in_array($name, ['power'/*, 'estimatedpowerstream'*/, 'estimatedrawpowerstream'/*, 'powercalcstream'*/]) || $param1 === 'power')  && strpos($formula, 'avg') === false){
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
        return empty(array_diff(Utl::getItem('athleteParams', self::getDescription($name, $formula, $param1), []), 
            array_keys($athlete))) && ($param1 === 'threshold' || $param2 === 'threshold')? !empty($athlete[self::$athleteParamsDescription[$name]['threshold']]) : true;
    }
    public static function requiredActivityParams($kpiName, $includeOptional = true){
        [$name, $formula, $param1] = array_pad(explode('_', $kpiName), 3, '');
        ['activityParams' => $activityParams, 'optionalActivityParams' => $optionalActivityParams, 'metrics' => $metrics] = Utl::getItems(['activityParams', 'optionalActivityParams', 'metrics'], self::getDescription($name, $formula), false, []);
        $metricsArray = $metrics ? (self::isMetricStream($param1) ? [$metrics, self::metricStream($param1)] : [$metrics]) : [];
        if ($includeOptional){
            $activityParams = array_merge($activityParams, $optionalActivityParams);
        }
        return $activityParams ? array_merge(array_values($activityParams), $metricsArray) : $metricsArray;
    }
    public static function hasRequiredActivityParams($kpiName, $activityColsToGet){
        return empty(array_diff(self::requiredActivityParams($kpiName, false), array_keys($activityColsToGet)));
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
        if (isset($description['optionalActivityParams'])){
            $arguments = array_merge($arguments, array_combine(array_keys($description['optionalActivityParams']), array_map(function($param) use ($activity) {return isset($activity[$param]) ? $activity[$param] : '';}, $description['optionalActivityParams'])));
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
                    $thresholdValue = round($athlete[self::$athleteParamsDescription[$name]['threshold']], $description['precision']);
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
                    $thresholdValue = round($athlete[self::$athleteParamsDescription[$name]['threshold']], $description['precision']);
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
                $arguments['smoothSeconds'] = $param1 ? $param1 : 1;
                break;
            case 'load':
                $arguments['smoothSeconds'] = $param1 ? $param1 : Utl::getItem('smoothSeconds', $arguments, 1);
        }
        return [self::$functionsMap[$formula], $arguments];
    }
    public function computeKpis($athleteId, $itemsToProcess, $itemsModel = null){
        $kpis = [];
        $objectsStore = Tfk::$registry->get('objectsStore');
        $athlete = array_filter($objectsStore->objectModel('sptathletes')->getOne(['where' => ['id' => $athleteId], 'cols' => ['hrmin', 'hrthreshold', 'ftp', 'speedthreshold', 'sex', 'weight']]));
        $stravaActivitiesModel = $objectsStore->objectModel('stravaactivities');
        if ($itemsModel){
            $controller = $objectsStore->objectController($itemsModel->objectName);
            $viewModel = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Save');
        }
        foreach($itemsToProcess as $key => $itemToProcess){
            $kpis[$key] = [];
            list('kpisToGet' => $kpisToGet, 'itemValues' => $itemValues) = Utl::getItems(['kpisToGet', 'itemValues'], $itemToProcess, [], []);
            if ($itemsModel && count($itemValues) > 2){
                $itemValues = $viewModel->viewToModel($itemValues, 'editToObj', false);
            }
            $kpisToCompute = [];  $kpisOKtoCompute = []; $activityColsToGet = []; 
            foreach($kpisToGet as $kpiName){
                [$name, $formula] = explode('_', $kpiName);
                $extendedName = Utl::getItem($name, self::$namesMap, $name);
                if ($kpiName === $extendedName){// rather than a kpi to compute, this is a missing item value (example: elevationgain when no gps data was recorded)
                    $kpis[$key][$kpiName] = false;
                }else{
                    if (self::hasRequiredAthleteParams($extendedName . '_' . $formula, $athlete)){
                        if ($name !== $extendedName && !Utl::getItem($extendedName, $itemValues)){
                            $kpisToCompute[] = $extendedName;
                            $activityColsToGet = array_merge($activityColsToGet, self::requiredActivityParams($extendedName));
                        }
                        $kpisToCompute[] = $kpiName;
                        $activityColsToGet = array_merge($activityColsToGet, self::requiredActivityParams($kpiName));
                    }else{
                        $kpis[$key][$kpiName] = false;
                    }
                }
            }
            if (!empty($kpisToCompute)){
                list('id' => $id, 'stravaid' => $stravaId) = Utl::getItems(['id', 'stravaid'], $itemValues);
                $activity = $itemValues;
                if (!empty($activityColsToGet)){
                    $activityColsToGet = array_unique($activityColsToGet);
                    if (!empty($activityColsToGet = array_diff($activityColsToGet, array_keys($activity)))){
                        if ($itemsModel && $id && count($itemModelColsToGet = array_intersect($activityColsToGet, $itemsModel->allCols)) > 0){
                            $activity = array_merge($activity, $itemsModel->getOne(['where' => $id ? ['id' => $id] : ['stravaid' => $stravaId], 'cols' => $itemModelColsToGet]));
                        }
                        if(!empty($activityColsToGet = array_diff($activityColsToGet, array_keys($activity))) && ($stravaColsToGet = array_intersect($activityColsToGet, $stravaActivitiesModel->allCols))){
                            if ($stravaId){
                                $activity = array_merge($activity, $stravaActivityValues = array_filter($stravaActivitiesModel->getOneCorrected(['where' => ['stravaid' => $stravaId], 'cols' => $stravaColsToGet])));
                            }
                            $kpisToCompute = array_diff($kpisToCompute, $stravaColsToGet);
                        }
                    }
                }
                foreach($kpisToCompute as $kpiName){
                    $requiredParams = self::requiredActivityParams($kpiName, false);
                    if (empty(array_diff($requiredParams, array_merge(array_keys($activity), $kpisOKtoCompute)))){
                        $kpisOKtoCompute[] = $kpiName;
                    }
                }
                foreach($activity as $col => &$param){
                    if (substr($col, -6) === 'stream' && is_string($param)){
                        $param = json_decode($param, true);
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
                    $activity[$kpiName] = KF::$funcName(...$arguments);
                    if (in_array($kpiName, $kpisToGet)){
                        $kpis[$key][$kpiName] = is_array($activity[$kpiName]) ? json_encode($activity[$kpiName]) : $activity[$kpiName];
                    }
                }
                $kpisWhichCouldNotBeComputed = array_diff($kpisToGet, $kpisOKtoCompute);
                foreach($kpisWhichCouldNotBeComputed as $kpiName){
                    $kpis[$key][$kpiName] = 0;
                }
            }
        }
        return $kpis;
    }
}
?>