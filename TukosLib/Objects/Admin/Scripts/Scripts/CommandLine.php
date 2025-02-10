<?php
namespace TukosLib\Objects\admin\Scripts\Scripts;

use TukosLib\TukosFramework as Tfk; 

class CommandLine {
    function __construct($id, $parameters, $ScriptObj, $runMode = "ATTACHED"){
        $cmd = is_array($parameters) ? implode(' ', $parameters) : $parameters;
        if ($runMode === 'ATTACHED'){
            $streamsStore = Tfk::$registry->get('streamsStore');
            $streamsStore->startStream($id, $cmd, $ScriptObj);
        }else{
            $this->execInBackground($cmd);
        }
    }
    function execInBackground($cmd) {//from https://gist.github.com/PaulKish/3f42805ae175237d79215cdae23991a2https://gist.github.com/PaulKish/3f42805ae175237d79215cdae23991a2, removed /B which is useless it seems)
        if (substr(php_uname(), 0, 7) == "Windows"){   
            $returnCode = pclose(popen("start ". $cmd, "r"));
        }
        else {
            $returnCode = exec($cmd . " > /dev/null &");
        }
    }
} 

?>
