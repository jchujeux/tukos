<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Google\Script;

class GoogleScriptTest1 {

    function __construct($parameters){ 
        $user         = Tfk::$registry->get('user');
        try{
            $options = new \Zend_Console_Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
            ]);
            $service = Script::getService();
            $request = new \Google_Service_Script_CreateProjectRequest();
            $request->setTitle('My Script');
            $response = $service->projects->create($request);
            
            $scriptId = $response->getScriptId();
            
            $code = <<<EOT
function helloWorld() {
  console.log('Hello, world!');
}
EOT;
            $file1 = new \Google_Service_Script_ScriptFile();
            $file1->setName('hello');
            $file1->setType('SERVER_JS');
            $file1->setSource($code);
            
            $manifest = <<<EOT
{
  "timeZone": "America/New_York",
  "exceptionLogging": "CLOUD"
}
EOT;
            $file2 = new \Google_Service_Script_ScriptFile();
            $file2->setName('appsscript');
            $file2->setType('JSON');
            $file2->setSource($manifest);
            
            $request = new \Google_Service_Script_Content();
            $request->setScriptId($scriptId);
            $request->setFiles([$file1, $file2]);
            
            $response = $service->projects->updateContent($scriptId, $request);
            echo "https://script.google.com/d/" . $response->getScriptId() . "/edit\n";$toto = 'toto';
            
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
