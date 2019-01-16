<?php
namespace TukosLib\Auth\Drivers;

use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Sql{
    public function __construct($config){
        $this->config = $config;
    }

    public function targetDb ($username, $password){
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
