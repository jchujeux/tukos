<?php
/**
 *
 * class for the scripts tukos object 
 */
namespace TukosLib\Objects\Admin\Scripts;

use TukosLib\Objects\AbstractModel;
use TukosLib\TukosFramework as Tfk; 

class Model extends AbstractModel {
    protected $runModeOptions       = ['ATTACHED', 'DETACHED'];
    protected $statusOptions        = ['DISABLED', 'READY', 'RUNNING'];
    public $colsToGet			= ['id', 'parentid', 'status', 'startdate', 'enddate', 'laststart', 'timeinterval', 'path', 'scriptname', 'parameters', 'runmode'];
    function __construct($objectName, $translator=null){
        $colsDefinition =  ['path'          =>  'VARCHAR(80)  DEFAULT NULL',
                            'scriptname'    =>  'VARCHAR(50)  DEFAULT NULL',
                            'parameters'    =>  'longtext ',
                            'runmode'       =>  "ENUM ('ATTACHED', 'DETACHED') ",
                            'status'        =>  "ENUM ('DISABLED', 'READY', 'RUNNING') ",
                            'startdate'     =>  "timestamp",
                            'enddate'       =>  "timestamp",
                            'timeinterval'  =>  'VARCHAR(80)',
                            'laststart'     =>  "timestamp",
                            'lastend'       =>  "timestamp",];
        parent::__construct($objectName, $translator, 'scripts', ['parentid' => ['users', 'networks']], [], $colsDefinition, [], ['runmode', 'status']);

        $this->initVals = ['parentid' => $this->user->id(), 'status' => 'DISABLED', 'path' => 'TukosLib\Objects\Admin\Scripts\Scripts\\', 'runmode' => 'ATTACHED', 'timeinterval' => json_encode([1, 'hour'])];
    }

    function initialize($init=[]){
        return parent::initialize(array_merge($this->initVals, $init));
    }

    function runModeOptions(){
        return ['ATTACHED', 'DETACHED'];
    }

    function statusOptions(){
        return ['DISABLED', 'READY', 'RUNNING'];
    }

    function addTimeInterval ($date, $interval){
        $result = date('Y-m-d H:i:s', strtotime('+' . $interval[0] . ' ' . $interval[1], strtotime($date)));
        return $result;
    }

    function okToStart($dates){
        $currentDate = date('Y-m-d H:i:s');
        if ((! empty($dates['enddate']) & $currentDate > $dates['enddate']) || (!empty($dates['startdate']) && $currentDate < $dates['startdate'])){
            return 'NOTBETWEENSTARTANDENDDATE'; 
        }else{
            $lastStart = ($dates['laststart'] === null ? '0000-00-00 00:00:00' : $dates['laststart']);
            $interval  = (empty($dates['timeinterval']) ? [1, 'hour'] :  json_decode($dates['timeinterval']));
            $timeInterval  = strtotime('+' . $interval[0] . ' ' . $interval[1]) - time();
            if ($timeInterval > 0){
                $currentTime            = strtotime($currentDate);
                $initialStartTime       = strtotime($dates['startdate']);
                $lastExpectedStartTime  = $initialStartTime + floor(($currentTime - $initialStartTime) / $timeInterval) * $timeInterval;
                $lastStartTime          = strtotime($lastStart);
                return ($lastStartTime < $lastExpectedStartTime ? true : 'NOTYETTIME');
            }else{
                return 'YES';
            }
        }
    }
    
    function processOne($where){
        return $this->processScript($this->getOne(['where' => $where, 'cols' => $this->colsToGet]));
    }
    
    function processScript($scriptInfo){
        switch ($scriptInfo['status']){
            case 'DISABLED' :
                return 'ScriptIsDisabled';
                break;
            case 'RUNNING' :
                return 'ScriptAlreadyRunning';
                break;
            case 'READY' :
                $okToStart = Tfk::isInteractive() || $this->okToStart($scriptInfo);
                if ($okToStart){ 
                    $scriptName = $scriptInfo['path'] . $scriptInfo['scriptname'];
                    $parameters = $scriptInfo['parameters'];
                    if (!strpos($parameters, '--parentid')){
                    	$parameters .= ' --parentid ' . $scriptInfo['id'];
                    }
                    $replace = [
                    	'/^ *([^ ]*)(.*)/' => function($matches){return $matches[1] . ' --app ' . Tfk::$registry->appName. ' ' . $matches[2];},
                    	'/([^\\b])@(\w*)([^\\b]|$)/' => function($matches) use ($scriptInfo){return ' ' . $scriptInfo[$matches[2]];}
                    ];
                    $script = new $scriptName($scriptInfo['id'], preg_replace_callback_array($replace, $scriptInfo['parameters']), $scriptInfo['runmode']);
                    return 'SCRIPTISRUNNING';
                }else{
                    return $okToStart;
                }
                break;
            default:
                return [$scriptInfo['status']];
        }
    }


    function process($idsToProcess){
        $result = [];
        foreach ($idsToProcess as $id){
            $result[] = [$id, $this->processScript($this->getOne(['where' => ['id' => $id], 'cols' => $this->colsToGet]))];
        }
        return $result;
    }
}
?>