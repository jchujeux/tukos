<?php
namespace TukosLib\Objects\Admin\Scripts\Scripts;

use TukosLib\TukosFramework as Tfk; 
use TukosLib\Utils\Feedback;

class PhpCommand extends CommandLine{
    function __construct($id, $parameters, $runMode){
        if (is_array($parameters)){
            $phpScript = Tfk::$tukosPhpDir . array_shift($parameters);
            $parameters = array_unshift($parameters, Tfk::$phpCommand, $phpScript);
        }else{
             $parameters = Tfk::$phpCommand . ' ' . Tfk::$tukosPhpDir . $parameters;
        }
        //Feedback::add("phpCommand - parameters: $parameters");
        parent::__construct($id, $parameters, $runMode);
    }
} 

?>
