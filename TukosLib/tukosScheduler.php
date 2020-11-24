<?php 
/**
 * tukos scheduler script
 */
namespace TukosLib;

use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

$phpDir = dirname(__DIR__) . '/';
require $phpDir . 'TukosLib/TukosFramework.php';
require $phpDir . 'TukosLib/cmdenv.php';

$params = getopt('', ['app:', 'class:', 'parentid:', 'db:', 'scriptids:']);

$appName = $params['app'];
$dbName = $params['db'];

echo "database: $dbName<br>";

Tfk::initialize('commandLine', $appName, $phpDir);

$configure =  '\\' . $appName . '\\Configure';

Tfk::$registry->set('appConfig', new $configure());
$appConfig = Tfk::$registry->get('appConfig');
$appConfig->dataSource['dbname'] = $dbName;
Tfk::setTranslator();
SUtl::instantiate();
$user = Tfk::$registry->get('user');

$objectsStore   = Tfk::$registry->get('objectsStore');
$user->setUser(['name' => 'tukosscheduler']);/* so as $user has the proper rights and other initialization information*/
$streamsStore = Tfk::$registry->get('streamsStore');

if (!empty($params['class'])){
    $className = $params['class'];
    $class = new $className($argv);
}else{
    $scripts         = $objectsStore->objectModel('scripts');
    $scriptsOutputs  = $objectsStore->objectModel('scriptsoutputs');
    $colsToGet = ['id', 'status', 'path', 'scriptname', 'parameters', 'startdate', 'enddate', 'laststart', 'timeinterval'];
    if ($scriptIds = Utl::getItem('scriptids', $params)){
        $scriptsToConsider = $scripts->getAll(['where' => [['col' => 'id', 'opr' => 'IN', 'values' => json_decode($scriptIds)]], 'cols' => $colsToGet]);
    }else{
        $scriptsToConsider = $scripts->getAll(['where' => [['col' => 'status', 'opr' => 'IN', 'values' => ['READY']]], 'cols' => $colsToGet]);
    }
    
    $feedback = [];
    foreach ($scriptsToConsider as $scriptInfo){
        $scriptInfo['runmode'] = 'ATTACHED';
        $feedback[$scriptInfo['id']] = $scripts->processScript($scriptInfo);
    }
    $runningIds = '';
    foreach($feedback as $id => $feedback){
        switch($feedback){
            case 'SCRIPTISRUNNING' :
                $runningIds .= ' ' . $id;
                break;
            default:
                break;
        }
    }
    $output  = ($runningIds === '' ? 'No script execution was started' : 'started execution of scripts: ' . $runningIds);
    
    if ($runningIds === ''){
        $values = $scriptsOutputs->getOne(['where' => ['parentid' => $user->id()], 'cols' => ['id', 'output', 'comments'], 'orderBy' => ['updated' => 'DESC']]);
        if (empty($values) || strpos($values['output'], 'No script') === false){
            $isInsert = true;
        }else{
            $isInsert = false;
            $values['output'] = $output;
            $values['parentid'] = $user->id();
            $values['comments'] = empty($values['comments']) ? "1 repeat" : (explode( ' ', $values['comments'])[0] + 1) . " repeats";
            $scriptsOutputs->updateOne($values);
        }
        if ($isInsert){
            $values = $scriptsOutputs->insert([/*'name' => 'tukos scheduler',*/ 'output' => $output, 'parentid' => $user->id()], true);
        }
    }
    $streamsStore->waitOnStreams();
    
    $values['output'] = '<br>tukos scheduler execution completed and all streams are closed';
    $scriptsOutputs->append($values, ['output', 'errors']);
}
?>
