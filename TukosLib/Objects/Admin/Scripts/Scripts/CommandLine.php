<?php
namespace TukosLib\Objects\admin\Scripts\Scripts;

use TukosLib\TukosFramework as Tfk; 

class CommandLine {
    function __construct($id, $parameters, $ScriptObj){
        $streamsStore = Tfk::$registry->get('streamsStore');
        $cmd = is_array($parameters) ? implode(' ', $parameters) : $parameters;
        $streamsStore->startStream($id, $cmd, $ScriptObj);
    }
} 

?>
