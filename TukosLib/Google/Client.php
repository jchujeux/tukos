<?php
namespace TukosLib\Google;

class Client {
    private static $client = null;
	public static function get($jsonCredentials = null){
		if (is_null(self::$client)){
		    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/tukos-f090e7fe437e.json');
		    self::$client = new \Google_Client();
			self::$client->setApplicationName('tukos');
			if (is_null($jsonCredentials)){
			    self::$client->useApplicationDefaultCredentials();
			}else{
			    self::$client->setAuthConfig($jsonCredentials);
			}
			self::$client->setScopes([
				'https://www.googleapis.com/auth/calendar', 'https://www.googleapis.com/auth/calendar.readonly', 
				'https://www.googleapis.com/auth/drive', 'https://www.googleapis.com/auth/drive.readonly', 
			    'https://www.googleapis.com/auth/spreadsheets', 'https://www.googleapis.com/auth/spreadsheets.readonly',
			    'https://www.googleapis.com/auth/script.projects', 'https://www.googleapis.com/auth/script.projects.readonly',
			    'https://www.googleapis.com/auth/gmail', 'https://www.googleapis.com/auth/gmail.send'
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
  			    $arguments[] = ['pageToken' => $pageToken];
  				$list = call_user_func_array($function, $arguments);
  				array_pop($arguments);
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