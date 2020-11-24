<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class FullClean {

    function __construct($parameters){ 
        $store        = Tfk::$registry->get('store');
        try{
            $options = new \Zend_Console_Getopt([
            	'app-s'		=> 'tukos application name (mandatory if run from the command line, not needed in interactive mode)',
                'db-s'		    => 'tukos application database name (not needed in interactive mode)',
                'class=s'          => 'this class name',
                'removeDelay-s'    => 'recent deleted ids to keep in php interval_spec format (optional: 0 if omitted)',
                'parentid-s'       => 'parent id (optional, default is user->id())',
            ]);
            if ($options->removeDelay){
                $deleteBefore = (new \DateTime)->sub(new \DateInterval($options->removeDelay))->format('Y-m-d H:i:s');
            }
            $objectsToConsider = Directory::getNativeObjs();
            $totalDeleted = 0;
            $idsToDelete = $store->getAll(['table' => 'tukos', 'cols' => ['id'],
                'where' => [['col' => 'id', 'opr' => '<', 'values' => 0], ['col' => 'updated' , 'opr' => '<', 'values' => $deleteBefore]]
            ]);
            $stmt = $store->query("DELETE FROM tukos WHERE tukos.id < 0 AND tukos.updated < '$deleteBefore'");
            $countDeleted = $stmt->rowCount();
            if ($countDeleted){
                $totalDeleted = $countDeleted;
                echo "<br>FullClean - $countDeleted removed ids in table tukos: " . implode(', ', array_column($idsToDelete, 'id'));
            }
            foreach ($objectsToConsider as $objectName){
            	$idsToDelete = [];
            	$countDeleted = 0;
            	if ($store->tableExists($objectName)){
            		$idsToDelete = $store->query("SELECT id FROM $objectName WHERE NOT EXISTS (SELECT NULL from tukos WHERE tukos.id = $objectName.id OR tukos.id = -$objectName.id)")->fetchAll(\PDO::FETCH_COLUMN, 0);
            		if (!empty($idsToDelete)){
            		    $countDeleted = $store->query("DELETE FROM $objectName WHERE NOT EXISTS (SELECT NULL from tukos WHERE tukos.id = $objectName.id OR tukos.id = -$objectName.id)")->rowCount();
            		    $totalDeleted += $countDeleted;
            		    echo "<br>FullClean - $countDeleted removed ids in table $objectName : they were not found in tukos: ". implode(', ', $idsToDelete);
            		    
            		}
            	}
            }
            if ($totalDeleted === 0){
               	echo "FullClean - no item had to be removed";
            }
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command arguments in FullClean: ', $e->getUsageMessage());
        }
    }
}
?>
