<?php
namespace TukosLib\Google;

use TukosLib\Google\Client;
class Sheets{
    private static $service = null;
	public static function getService(){
		if (is_null(self::$service)){
			self::$service = new \Google_Service_Sheets(Client::get());
		}
		return self::$service;
    }

    public static function getValues($spreadsheetId, $range){
    	$response = self::getService()->spreadsheets_values->get($spreadsheetId, $range);
    	$values =  $response->getValues();
    	return $values;
    }
        
}
?>