<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
//use Zend\Console\Getopt;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class UpdateObjectTablesStructure {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');
        $tukosModel   = Tfk::$registry->get('tukosModel');
        try{
            $options = new \Zend_Console_Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
            ]);
            $objectsToConsider = array_intersect(Directory::getObjs(), $store->hook->fetchTableList());
            $changesWereMade = false;
            foreach ($objectsToConsider as $objectName){
                try {
                    $objectModel = $objectsStore->objectModel($objectName);
                    $colsDescription = $objectModel->colsDescription;
                    $tableCols = $store->tableCols($objectName);
                    $objectCols = array_keys($colsDescription);
                    //$tableCols = array_keys($tableCols);
                    $commonCols = array_intersect($objectCols, $tableCols);
                    $missingTableCols = array_diff($objectCols, $commonCols);
                    $extraTableCols = array_diff($tableCols, $commonCols);
                    if (!empty($missingTableCols)){
                    	$changesWereMade = true;
                    	$store->addCols(array_intersect_key($colsDescription, array_flip($missingTableCols)), $objectName);
                    	echo 'table: ' . $objectName . ' - Missing table cols added: ' . json_encode($missingTableCols) . '<br>';
                    }
                    if (!empty($extraTableCols)){
                    	$changesWereMade = true;
                    	echo 'table: ' . $objectName . ' - extra table cols(can be removed): ' . json_encode($extraTableCols) . '<br>';
                    }
                }catch(\Exception $e){
                    Tfk::error_message('on', ' Exception in TukosTableInit: ', $e->getMessage());
                }
            }
            if (!$changesWereMade){
               echo 'all tables are OK - no change made';
            }
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
