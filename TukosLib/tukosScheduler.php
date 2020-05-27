<?php 
/**
 * tukos scheduler script
 */
namespace TukosLib;

use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;

$phpDir = dirname(__DIR__) . '/';
require $phpDir . 'TukosLib/TukosFramework.php';
require $phpDir . 'TukosLib/cmdenv.php';

$params = getopt('', ['app:', 'class:', 'parentid:', 'db:']);

$appName = $params['app'];
$dbName = $params['db'];

Tfk::initialize('commandLine', $appName, $phpDir);

$configure =  '\\' . $appName . '\\Configure';

Tfk::$registry->set('appConfig', new $configure());
$appConfig = Tfk::$registry->get('appConfig');
$appConfig->dataSource['dbname'] = $dbName;
/*
if (!empty($tukosSchedulerDb = Tfk::$registry->get('configStore')->getOne(['where' => [$appConfig->configSource['username_col'] => 'tukosscheduler'], 'cols' => ['targetdb'], 'table' => 'usersauth'])['targetdb'])){
    $appConfig->dataSource['dbname'] = $tukosSchedulerDb;
}
*/
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
    
    if (count($argv) === 1){
        $scriptsToConsider = $scripts->getAll(['where' => [['col' => 'status', 'opr' => 'IN', 'values' => ['READY']]], 'cols' => ['*']]);
    }else{
        unset($argv[0]);
        $scriptsToConsider = $scripts->getAll(['where' => [['col' => 'id', 'opr' => 'IN', 'values' => $argv]], 'cols' => ['*']]);
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
    $output  = ($runningIds === '' ? 'No script execution was started' : 'started executions of scripts: ' . $runningIds);
    
    $values = $scriptsOutputs->insert([/*'name' => 'tukos scheduler', */'output' => $output, 'parentid' => ['id' => $user->id(), 'object' => 'users']], true);
    $streamsStore->waitOnStreams();
    
    $values['output'] = '<br>tukos scheduler execution completed and all streams are closed';
    $scriptsOutputs->append($values, ['output', 'errors']);
}
?>
