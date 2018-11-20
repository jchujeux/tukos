<?php
namespace TukosLib\Google;

use TukosLib\Google\Client;
class Script{
    private static $service = null;
	public static function getService(){
	    if (is_null(self::$service)){
	        $client = Client::get();
	        $client->setAuthConfig(__DIR__ . \client_secret_tukos.json);
		    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
		        $client->setAccessToken($_SESSION['access_token']);
		        $client->setAccessType("offline");
		        self::$service = new \Google_Service_Script($client);
		    } else {
		        $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
		        header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
		    }
		}
		return self::$service;
    }
    public static function get($scriptId, $optParams=[]){
        $response = self::getService()->projects->get($scriptId, $optParams);
        return $response;
    }
    
        
}
?>