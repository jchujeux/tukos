<?php
namespace TukosLib\Objects\Admin\Scripts\Scripts;

use TukosLib\TukosFramework as Tfk; 
use TukosLib\Utils\Feedback;

class PhpCommand extends CommandLine{
    function __construct($id, $parameters, $scriptObj){
        if (is_array($parameters)){
            $phpScript = Tfk::$tukosPhpDir . array_shift($parameters);
            $parameters = array_unshift($parameters, 'php', $phpScript);
        }else{
             $parameters = 'php' . ' ' . Tfk::$tukosPhpDir . $parameters;
        }
        parent::__construct($id, $parameters, $scriptObj);
    }
} 

?>
