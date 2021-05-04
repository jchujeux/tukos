<?php
namespace TukosLib\Objects\Admin\Scripts;
/*
 * "Stolen with pride" from http://www.ibm.com/developerworks/library/os-php-multitask/, and adapted to tukos
 */
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class StreamsManager{
   /*
    * On my Windows system (in french), mb_detect_encoding wrongly detects ISO-8859-1 as the pipe output encoding, when the proper encoding is CP850,
    * hence the parameter outputEncoding below that must be checked for each specific server set-up.
    */
    function __construct($timeout = 2, $outputEncoding='CP850'){
        $this->user  = Tfk::$registry->get('user');
        $this->objectsStore = Tfk::$registry->get('objectsStore');
        $this->timeout = $timeout;
        $this->process = [];
        $this->outputId = [];
        $this->scriptObj = $this->objectsStore->objectModel('scripts');
        $this->scriptsOutputs = $this->objectsStore->objectModel('scriptsoutputs');
        $this->outputEncoding = $outputEncoding;
    }
   /*
    * Launches a new command
    */ 
    public function startStream($id, $cmd, $scriptObj = false){
        $desc = [0 => ["pipe", "r"], 1 => ["pipe", "w"], 2 => $scriptObj ? ["pipe", "w"] : ["file", Tfk::$tukosTmpDir . $id . "streamerror.txt", 'a']];
        if (empty($this->process[$id])){
            try{
                //Feedback::add("streamsManager - osName: " . Tfk::osName() . " cmd: $cmd");
                $this->process[$id]['resource'] = proc_open(Tfk::osName() === 'Linux' ? addslashes($cmd) : $cmd, $desc, $pipes);
                if ($scriptObj){
                    $lockedMode = $this->user->getLockedMode();
                    $this->user->setLockedMode(false);
                    $this->scriptObj->updateOne(['id' => $id, 'status' => 'RUNNING', 'laststart' => date('Y-m-d H:i:s')]);
                    $this->user->setLockedMode($lockedMode);
                }
                $this->process[$id]['pipes'] = $pipes;
                return true;
            } catch (\Exception $e){
                Feedback::add('Error in startStresm: ' . $e->getMessage());
                return false;
            }
        }else{
            return false;
        }
    }
    public function closeStream($id, $scriptObj = true){
        foreach ($this->process[$id]['pipes'] as $pipe){
            fclose($pipe);
        }
       proc_close($this->process[$id]['resource']);
        if ($scriptObj){
            $lockedMode = $this->user->getLockedMode();
            $this->user->setLockedMode(false);
            $this->scriptObj->updateOne(['id' => $id, 'status' => 'READY', 'lastend' => date('Y-m-d H:i:s')]);
            $this->user->setLockedMode($lockedMode);
        }
        unset($this->process[$id]); 
    }
    function getPipesContent($theProcess, $outputFlag, $id, $result){
        $read       = [$theProcess['pipes'][1]];
        $write      = [];
        $exception  = [];
        stream_select($read, $write, $exception, $this->timeout); 
        $values['output'] = mb_convert_encoding(stream_get_contents($theProcess['pipes'][1]), 'UTF-8', $this->outputEncoding);
        if (isset($theProcess['pipes'][2])){
        	$values['errors'] = mb_convert_encoding(stream_get_contents($theProcess['pipes'][2]), 'UTF-8', $this->outputEncoding);
        }
        switch ($outputFlag){
            case 'return':
                if (isset($result['output'])){
                    $result['output'] .= $values['output'];
                    if (!empty($values['errors'])){
                    	$result['errors'] .= $values['errors'];
                    }
                 }else{
                    $result['output'] = $values['output'];
                    if (!empty($values['errors'])){
                    	$result['errors'] = $values['errors'];
                    }
                 }   
                break;
            case 'store':
                if (isset($this->outputId[$id])){
                    $values['id'] = $this->outputId[$id];
                    $this->scriptsOutputs->append($values, ['output', 'errors']);
                }else{
                    $values['permission'] = 'RO';
                    $values['parentid'] = $id;
                    $result = $this->scriptsOutputs->insertExtended($values, true);
                    $this->outputId[$id] = $result['id'];
                }
                break;
            case 'forget':
            default:
                break;
        }
        return (isset($result) ? $result : true);
    }
    public function waitOnStream($id, $scriptObj = true, $outputFlag = 'store'){
        $continue = true;
        $result = null;
        while($continue){
            $theProcess = $this->process[$id];
            $status = proc_get_status($theProcess['resource']);
            if (! $status['running']){
                $continue = false;
            }
            $result = $this->getPipesContent($theProcess, $outputFlag, $id, $result);
        }
        $this->closeStream($id, $scriptObj);
        return (isset($result) ? $result : true);
    }
    public function waitOnStreams($seconds = 1){    
        if ($this->process){
            while (count($this->process)) { 
                sleep($seconds);
                foreach ($this->process as $id => $theProcess){
                    $status = proc_get_status($theProcess['resource']);
                    $theProcessTerminated =  ! $status['running'];
    
                    $this->getPipesContent($theProcess, 'store', $id, null);
                    
                    if ($theProcessTerminated){ 
                        $this->closeStream($id, true);
                    }
                }
            }
        }
    }
}
?>
