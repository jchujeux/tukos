<?php
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
                'app-s'		=> 'tukos application name (mandatory if run from the command line, not needed in interactive mode)',
                'db-s'		    => 'tukos application database name (not needed in interactive mode)','class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
                'rootUrl-s'		=> 'https://tukos.site or https://localhost, omit if interactive',
                'modifycols-s' => 'if "true" the cols definition will be modified as needed',
                'removecols-s' => 'if "true" the cols will be removed as needed',
            ]);
            $objectsToConsider = array_merge(array_intersect(Directory::getNativeObjs(), $store->tableList()), ['tukos']);
            
            $changesWereMade = false;

            foreach ($objectsToConsider as $objectName){
                try {
                    $objectModel = $objectName === 'tukos' ? $tukosModel : $objectsStore->objectModel($objectName);
                    $colsDescription = $objectModel->colsDescription;
                    $columnsStructure = Utl::toAssociative($store->tableColsStructure($objectName), 'Field');
                    $tableCols = $store->tableCols($objectName);
                    $objectCols = array_keys($colsDescription);
                    $commonCols = array_intersect($objectCols, $tableCols);
                    $missingTableCols = array_diff($objectCols, $commonCols);
                    $extraTableCols = array_diff($tableCols, $commonCols);
                    if (!empty($missingTableCols)){
                    	$changesWereMade = true;
                    	$store->addCols(array_intersect_key($colsDescription, array_flip($missingTableCols)), $objectName);
                    	echo '<br>table: ' . $objectName . ' - Missing table cols added: ' . json_encode($missingTableCols) . '<br>';
                    }
                    foreach ($extraTableCols as $col){
                    	echo "<br>table: $objectName: $col can be removed";
                    	if ($options->removecols === "true"){
                    	    try{
                    	        $alterStmt = $store->pdo->query("ALTER TABLE `$objectName` DROP `$col`");
                    	        echo " => done";
                    	        $changesWereMade = true;
                    	    } catch (\PDOException $e){
                    	        echo '<br>Error removing  column ' . $col . ' in table ' . $objectName . '. Error message: '. $e->getMessage();
                    	    }
                    	}
                    }
                    // now loop on existing cols to detect differences
                    foreach ($commonCols as $col){
                        $appColDescription = str_ireplace(['not null', 'default null', 'null', ' '], '', $colsDescription[$col]);
                        $dbColStructure = $columnsStructure[$col];
                        $dbColDescription = str_replace(' ', '', $dbColStructure['Type'] . (is_null($default = $dbColStructure['Default']) ? '' : ("default '" . $default) . "'") . ($dbColStructure['Key'] === 'PRI' ? 'primarykey' : ''));
                        if (strcasecmp(substr($dbColDescription, 0, strlen($appColDescription)), $appColDescription) !== 0){
                            echo "<br>table: $objectName, col: $col, database definition: $dbColDescription, application definition: $appColDescription";
                            if ($options->modifycols === 'true'){
                                try{
                                    $colDesc = $colsDescription[$col];
                                    $alterStmt = $store->pdo->query("ALTER TABLE `$objectName` MODIFY COLUMN `$col` $colDesc");
                                    echo " => database column definition modified";
                                    $changesWereMade = true;
                                } catch (\PDOException $e){
                                    echo nl2br("\nError modifying  column $col in table $objectName - Error message: $e->getMessage()");
                                }
                            }
                            echo nl2br("\n");
                        }
                    }
                }catch(\Exception $e){
                    Tfk::error_message('on', ' Exception in UpdateObjectTablesStructure: ', $e->getMessage());
                }
            }
            echo $changesWereMade ? "<b>Changes were made!!!" : "<b>No change made";
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
