<?php
namespace TukosLib\Google;

use TukosLib\Google\Client;
class Gmail{
    private static $service = null;
	public static function getService(&$tokenSource){
	    if (is_null(self::$service)){
    	    $client = Client::get();
    	    $accessToken = (is_string($tokenSource) && file_exists($tokenSource)) ? json_decode(file_get_contents($tokenSource), true) : $tokenSource;
    	    $client->setAccessToken($accessToken);
    	    // If there is no previous token or it's expired.
    	    if ($client->isAccessTokenExpired()) {
    	        // Refresh the token if possible, else fetch a new one.
    	        if ($client->getRefreshToken()) {
    	            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    	        } else {
    	            throw new \Exception('Couldnotrefreshtoken');
    	        }
    	        // Save the token to a file.
    	        if (is_string($tokenSource)){
        	        if (!file_exists(dirname($tokenSource))) {
        	            mkdir(dirname($tokenSource), 0700, true);
        	        }
        	        file_put_contents($tokenSource, json_encode($client->getAccessToken()));
    	        }else{
    	            $tokenSource = $client->getAccessToken();
    	        }
    	    }
    	    self::$service = new \Google_Service_Gmail($client);
		}
		return self::$service;
    }
}
?>