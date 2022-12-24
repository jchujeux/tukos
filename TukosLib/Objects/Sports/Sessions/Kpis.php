<?php
namespace TukosLib\Objects\Sports\Sessions;

use TukosLib\Objects\Sports\KpisFormulaes as KF;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

trait Kpis {

    public static $beta = 2.6, $refCadence = 180;
    
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
    public static function kpiDescription($name, $athlete){
        $kpiName = explode('_', $name);
        if (empty($kpiName[2])){
            return [
                'heartrate_avgload' => ['formula' => 'avgLoad', 'arguments' => ['metrics' => 'avghr', 'threshold' => $athlete['hrthreshold'], 'minutesActive' => 'timemoving', 'sex' => $athlete['sex'], 'min' => $athlete['hrmin']]],
                'heartrate_load' => ['formula' => 'load', 'arguments' => ['metrics' => 'heartratestream', 'threshold' => $athlete['hrthreshold'], 'sex' => $athlete['sex'], 'min' => $athlete['hrmin']]],
                'power_load' => ['formula' => 'load', 'arguments' => ['metrics' => 'wattsstream', 'threshold' => $athlete['ftp'], 'sex' => $athlete['sex'], 'smoothSeconds' => 30]],
                'mechanical_load' => ['formula' => 'load', 'arguments' => ['metrics' => 'velocity_smoothstream', 'threshold' => $athlete['speedthreshold'], 'sex' => $athlete['sex'], 'b' => ['cadenceCorrection']]],
            ][$name];
        }else{
            return [
                "$kpiName[0]_timeabove_$kpiName[2]" => ['formula' => 'timeAbove', 'arguments' => ['metrics' => "$kpiName[0]stream", 'threshold' => self::getThreshold($kpiName, 2, $athlete), 'uncertainty' => 0.02, 'fuzzyType' => 'relative']],
                "$kpiName[0]_timebelow_$kpiName[2]" => ['formula' => 'timeBelow', 'arguments' => ['metrics' => "$kpiName[0]stream", 'threshold' => self::getThreshold($kpiName, 2, $athlete), 'uncertainty' => 0.02, 'fuzzyType' => 'relative']],
            ][$name];
        }
    }
	
    public function getKpis($query, $kpisToGet){
        $kpis = [];
        $athlete = Tfk::$registry->get('objectsStore')->objectModel('sptathletes')->getOne(['where' => ['id' => $query['athlete']], 'cols' => ['hrmin', 'hrthreshold', 'ftp', 'speedthreshold', 'sex']]);
        foreach($kpisToGet as $sessionId => $sessionKpisToGet){
            $kpisDescription = []; $streamsToGet = [];
            foreach($sessionKpisToGet as $kpiName){
                $kpisDescription[$kpiName] = $this->kpiDescription($kpiName, $athlete);
                $streamsToGet[] = $kpisDescription[$kpiName]['arguments']['metrics'];
            }
            $streamsToGet = array_unique($streamsToGet);
            $streams = $this->getOne(['where' => ['id' => $sessionId], 'cols' => $streamsToGet]);
            foreach($kpisDescription as $kpiName => $description){
                if (!empty($metrics = $streams[$description['arguments']['metrics']])){
                    $description['arguments']['metrics'] = json_decode($metrics);
                    if (Utl::getItem('b', $description['arguments']) === 'cadenceCorrection'){
                        if (!empty($streams['cadencestream'])){
                            $correction = self::$beta * self::$refCadence;
                            foreach(json_decode($streams['cadencestream']) as $cadence){
                                $b[] = $correction / ($cadence > 50 ? $cadence : 50);
                            }
                            $description['arguments']['b'] = $b;
                        }else{
                            $description['arguments']['b'] = self::$beta;
                        }
                    }
                    $funcName = $description['formula'];
                    $kpis[$sessionId][$kpiName] = KF::$funcName(...$description['arguments']);
                }
            }
            $this->updateOne(newValues: ['id' => $sessionId, 'kpiscache' => Utl::getItem($sessionId, $kpis, 0, 0)], jsonFilter: true);
        }
        return ['data' => ['kpis' => $kpis]];
    }
}
?>