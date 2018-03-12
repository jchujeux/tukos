<?php
namespace TukosLib\Auth\Drivers;

use TukosLib\Objects\Admin\Users\Model as UsersModel;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Sql{
    public function __construct($config){
        $this->config = $config;
    }

    public function targetDb ($username, $password){
/*
    	$tk = SUtl::$tukosTableName;
        $tu = $this->config['table'];
    	$store = SUtl::$store;
        if (!$store->tableExists($tu)){
            if (!$username === 'tukos'){
            	return false;
            }else{
        		$store->createTable('users', array_merge([ 'id'  =>  'INT(11) NOT NULL '], UsersModel::$colsDefinition), 'PRIMARY KEY (`ID`)');
        		$store->insert(['id' => 0, 'password' => $password, 'rights' => 'SUPERADMIN'], ['table' => 'users']);
            	return 0;
            }
        }
*/
    	$configStore = Tfk::$registry->get('configStore');
   		$result = $configStore->getOne(['where' => [$this->config['username_col'] => $username, $this->config['password_col'] => $password], 'cols' => ['targetdb'], 'table' => 'users']);
	    if (is_array($result) && !empty($result['targetdb'])){
	        return $result['targetdb'];
	    }else{
	        Feedback::add('Wrong username and/or password - Please re-enter or contact your administrator');
	        return false;
	    }
    }

}
?>
