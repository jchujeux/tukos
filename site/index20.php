<?php
/**
 * The entry point to route URL requests towards the known tukos 2.0 applications. 
 * Tukos 2.0 relies on the Aura system on the php side, and on the dojo framework on the javascript side
 */

use TukosLib\TukosFramework as Tfk;

require '/tukos/tukosLib/TukosFramework.php';

Tfk::initialize('interactive');

if (Tfk::$registry->route) {
    if (Tfk::$registry->appName){
         $application = strtolower(Tfk::$registry->appName);
         $applicationClass = ['tukosapp' => 'TukosApp', 'tukossports' => 'TukosSports', 'tukosbus' => 'TukosBus'];
         if (in_array($application, array_keys($applicationClass))){
            $appName = $applicationClass[$application];
            $configure = $appName . '\\Configure';
            $mainController = '\\TukosLib\\Controllers\\Main';

            Tfk::$registry->set('appConfig', new $configure()); 

            $mainExec = new $mainController(Tfk::$registry->route->values, Tfk::$registry->urlQuery);

         }else{
            echo '<p> unknown application: ' . Tfk::$registry->appName;
         };
    } else{
        echo "No application was found for that URI path.";
    }
}else{
    echo "No route was found for that URI path.";
}
?>
