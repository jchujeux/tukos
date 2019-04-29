<?php
/**
 * The entry point to route URL requests towards the known tukos 2.0 applications. 
 * Tukos 2.0 relies on the Aura system on the php side, and on the dojo framework on the javascript side
 */

use TukosLib\TukosFramework as Tfk;

$phpDir = getenv('tukosPhpDir');

require $phpDir . '/TukosLib/TukosFramework.php';

Tfk::initialize('interactive', null, $phpDir);

if (Tfk::$registry->route) {
    if ($appName = Tfk::$registry->appName){
        $configure = $appName . '\\Configure';
        $mainController = '\\TukosLib\\Controllers\\Main';

        Tfk::$registry->set('appConfig', new $configure()); 

        $mainExec = new $mainController(Tfk::$registry->route->values, Tfk::$registry->urlQuery);
    }else{
        echo "No application was found for that URI path.";
    }
}else{
    echo "No route was found for that URI path.";
}
?>
