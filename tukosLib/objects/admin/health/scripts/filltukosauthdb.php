<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
//use Zend\Console\Getopt;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class FillTukosAuthDb {

    function __construct($parameters){ 
        $storeDbName    = Tfk::$registry->get('appConfig')->dataSource['dbname'];
        $user         = Tfk::$registry->get('user');
        $configStore        = Tfk::$registry->get('configStore');
        $objectsStore = Tfk::$registry->get('objectsStore');
        $tukosModel   = Tfk::$registry->get('tukosModel');
        try{
/*
        	$options = new \Zend_Console_Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
            ]);
*/
            $usersInfo = $objectsStore->objectModel('users')->getAll(['cols' => ['name', 'password']]);
            foreach ($usersInfo as $user){
            	$user['username'] = Utl::extractItem('name', $user);
            	$user['targetdb'] = $storeDbName;
	            if (empty($configStore->getOne(['table' => 'users', 'where' => ['username' => $user['username']], 'cols' => ['username']]))){
            		$configStore->insert($user, ['table' => 'users']);
            		echo 'would copy item: ' . var_dump($user);
	            }else{
	            	echo 'user ' . $user['username'] . ' already exists. Not copied' . PHP_EOL;
	            }
            }
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
