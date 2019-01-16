<?php
namespace TukosLib\Objects\admin\Scripts\Scripts;

use TukosLib\TukosFramework as Tfk;

class Example{
    function __construct($id, $parameters){
        Tfk::debug_mode('log', 'scripts\example.php - id, parameters', [$id, $parameters]);
    }
}

?>
