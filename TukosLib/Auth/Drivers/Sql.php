<?php
namespace TukosLib\Auth\Drivers;

use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Sql{
    public function __construct($config){
        $this->config = $config;
    }
    public function getUser($username, $password){
        $configStore = Tfk::$registry->get('configStore');
        return $configStore->getOne(['where' => [$this->config['username_col'] => $username, $this->config['password_col'] => $password], 'cols' => ['targetdb'], 'table' => 'usersauth']);
    }
    public function targetDb ($username, $password){
	    $result = $this->getUser($username, $password);
	    if (is_array($result)){
	        return empty($targetDb = $result['targetdb']) ? Tfk::$registry->get('appConfig')->dataSource['dbname'] : $targetDb;
	    }else{
	        Feedback::add(Tfk::tr('Wrongusernamepassword'));
	        return false;
	    }
    }
    public function googleUserTargetDb($username){
        $configStore = Tfk::$registry->get('configStore');
        $result = $configStore->getOne(['where' => [$this->config['username_col'] => $username], 'cols' => ['targetdb'], 'table' => 'usersauth']);
        if (is_array($result)){
            return empty($targetDb = $result['targetdb']) ? Tfk::$registry->get('appConfig')->dataSource['dbname'] : $targetDb;
        }else{
            Feedback::add(Tfk::tr('googletukosnamemismatch') . ': ' . $username);
            return false;
        }
    }
}
?>
