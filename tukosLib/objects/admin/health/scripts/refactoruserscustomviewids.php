<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;

use Zend\Console\Getopt;

use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class RefactorUsersCustomViewIds {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');

        try{
            $options = new \Zend_Console_Getopt([
            	'app-s'		=> 'tukos application name (mandatory if run from the command line, not needed in interactive mode)',
            ]);
            $itemsToRefactor = $store->getAll(['table' => 'users', 'cols' => ['id', 'customviewids'], 'where' => [
            	['col' => 'id', 'opr' => '>', 'values' => 0], ['col' => 'customviewids', 'opr' => 'IS NOT NULL', 'values' => null]
            ]]);
            array_walk($itemsToRefactor, function($item) use ($store) {
            	$customViewIds = json_decode($item['customviewids'], true);
            	if (!empty($customViewIds)){
	            	array_walk($customViewIds, function(&$objectCustomViewIds){
	            		$newObjectCustomViewIds = [];
	            		foreach($objectCustomViewIds as $view => $customViewId){
	            			$newObjectCustomViewIds[$view] = ['tab' => $customViewId];
	            		}
	            		$objectCustomViewIds = $newObjectCustomViewIds;
	            	});
            	}else{
            		$customViewIds = null;
            	}
            	$store->update(['customviewids' => is_null($customViewIds) ? $customViewIds : json_encode($customViewIds)], ['where' => ['id' => $item['id']], 'table' => 'users']);
            });
            echo "Done !";
        }catch(Getopt_exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command arguments in RefactorCustomizaton: ', $e->getUsageMessage());
        }
    }
}
?>
