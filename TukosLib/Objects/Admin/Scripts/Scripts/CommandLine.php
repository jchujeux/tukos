<?php
namespace TukosLib\Objects\admin\Scripts\Scripts;

use TukosLib\TukosFramework as Tfk; 

class CommandLine {
    function __construct($id, $parameters, $runMode){
        $streamsStore = Tfk::$registry->get('streamsStore');
        $cmd = is_array($parameters) ? implode(' ', $parameters) : $parameters;
        $streamsStore->startStream($id, $cmd, $runMode);
    }
} 

?>
