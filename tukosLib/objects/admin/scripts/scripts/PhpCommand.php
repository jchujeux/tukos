<?php
namespace TukosLib\Objects\admin\Scripts\Scripts;

use TukosLib\TukosFramework as Tfk; 

class PhpCommand extends CommandLine{
    function __construct($id, $parameters, $runMode){
        if (is_array($parameters)){
            $phpScript = Tfk::phpTukosDir . array_shift($parameters);
            $parameters = array_unshift($parameters, Tfk::phpCommand, $phpScript);
        }else{
             $parameters = Tfk::phpCommand . Tfk::phpTukosDir . $parameters;
        }
        parent::__construct($id, $parameters, $runMode);
    }
} 

?>
