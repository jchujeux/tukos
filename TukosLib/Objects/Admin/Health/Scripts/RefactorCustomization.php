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

class RefactorCustomization {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');

        try{
            $options = new \Zend_Console_Getopt([
            	'app-s'		=> 'tukos application name (mandatory if run from the command line, not needed in interactive mode)',
            ]);
            $itemsToRefactor = $store->getAll(['table' => 'tukos', 'cols' => ['id', 'custom'], 'where' => [
            	['col' => 'id', 'opr' => '>', 'values' => 0], ['col' => 'custom', 'opr' => 'IS NOT NULL', 'values' => null], ['col' => 'custom', 'opr' => 'RLIKE', 'values' => 'edit']
            ]]);
            array_walk($itemsToRefactor, function($item) use ($store) {
            	$oldCustomization = json_decode($item['custom'], true);
            	if (!empty($oldCustomization)){
	            	$viewCustomization = Utl::extractItem('edit', $oldCustomization);
	            	if (!empty($viewCustomization)){
	            		$newCustomization = json_encode(array_merge($oldCustomization, ['edit' => ['tab' => $viewCustomization]]));
	            	}
            	}else{
            		$newCustomization = null;
            	}
            	$store->update(['custom' => $newCustomization], ['where' => ['id' => $item['id']], 'table' => 'tukos']);
            });
            echo "Done !";
        }catch(Getopt_exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command arguments in RefactorCustomizaton: ', $e->getUsageMessage());
        }
    }
}
?>
