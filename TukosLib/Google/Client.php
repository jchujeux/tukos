<?php
namespace TukosLib\Google;

class Client {
    private static $client = null;
	public static function get(){
		if (is_null(self::$client)){
		    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/tukos-f090e7fe437e.json');
		    self::$client = new \Google_Client();
			self::$client->setApplicationName('tukos');
			self::$client->useApplicationDefaultCredentials();
			//self::$client->setAssertionCredentials($credentials);
			//$credentials = self::$client->loadServiceAccountJson('tukos-f090e7fe437e.json', 'https://www.googleapis.com/auth/calendar');
			//self::$client->setAuthConfig(__DIR__ . '\tukos-f090e7fe437e.json');
			self::$client->setScopes([
				'https://www.googleapis.com/auth/calendar', 'https://www.googleapis.com/auth/calendar.readonly', 
				'https://www.googleapis.com/auth/drive', 'https://www.googleapis.com/auth/drive.readonly', 
			    'https://www.googleapis.com/auth/spreadsheets', 'https://www.googleapis.com/auth/spreadsheets.readonly',
			    'https://www.googleapis.com/auth/script.projects', 'https://www.googleapis.com/auth/script.projects.readonly'
			]);
		}
		return self::$client;
    }
    public static function iterate($service, $resource, $method, $callback, $arguments = [], $getItemsMethod = 'getItems'){
    	$function = [$service->$resource, $method];
    	$list = call_user_func_array($function, $arguments);
    	while(true) {
  			$items = $list->$getItemsMethod();
    		foreach ($items as $item) {
    			$callback($item);
  			}
  			$pageToken = $list->getNextPageToken();
  			if ($pageToken) {
  				array_push($arguments, array_merge(array_pop($arguments), ['pageToken' => $pageToken]));
  				$list = call_user_func_array($function, $arguments);
  			} else {
    			break;
  			}
    	}
    }

    public static function getList($service, $resource, $method, $callback = null,$arguments = [], $getItemsMethod = 'getItems'){
    	$result = [];
    	if (is_null($callback)){
    		$callback = function($item){return $item;};
    	}
    	$arrayCallback = function($item) use (&$result, $callback){
    		$result[] = $callback($item);
    	};
    	self::iterate($service, $resource, $method, $arrayCallback, $arguments, $getItemsMethod);
    	return $result;
    }
}
?>