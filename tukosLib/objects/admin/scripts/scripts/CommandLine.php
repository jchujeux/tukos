<?php
namespace TukosLib\Objects\admin\Scripts\Scripts;

use TukosLib\TukosFramework as Tfk; 

class CommandLine {
    function __construct($id, $parameters, $runMode){
        $streamsStore = Tfk::$registry->get('streamsStore');
        switch ($runMode){
            case 'ATTACHED':
                $cmd = (is_array($parameters) ? implode(' ', $parameters) : $parameters);
                break;
            case 'DETACHED':
                $cmd = Tfk::phpDetachedCommand . Tfk::$phpTukosDir . 'tukosLib\tukosScheduler.php ' . (is_array($id) ? implode(' ', $id) : ' ' . $id);
                break;
            default:
                Tfk::debug_mode('error', 'unknown runMode: ', $runMode);
                break;
        }                
        //Tfk::debug_mode('log', 'CommandLine - id, cmd, runMode: ', [$id, $cmd, $runMode]);
        $streamsStore->startStream($id, $cmd, $runMode);
    }
} 

?>
