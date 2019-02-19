<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class FullClean {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');

        try{
            $options = new \Zend_Console_Getopt([
            	'app-s'		=> 'tukos application name (mandatory if run from the command line, not needed in interactive mode)',
                'class=s'          => 'this class name',
                'deleteDelay-s'    => 'recent deleted ids to keep in php interval_spec format (optional: 0 if omitted)',
                'parentid-s'       => 'parent id (optional, default is user->id())',
            ]);
            if ($options->deleteDelay){
                $deleteBefore = (new \DateTime)->sub(new \DateInterval($options->deleteDelay))->format('Y-m-d H:i:s');
            }
            $objectsToConsider = Directory::getNativeObjs();
            $totalDeleted = 0;
            foreach ($objectsToConsider as $objectName){
            	$idsToDelete = [];
            	$countDeleted = 0;
            	if ($store->tableExists($objectName)){
            		$idsToDelete = $store->getAll(['table' => 'tukos', 'join' => [['inner', $objectName, "tukos.id = - $objectName.id"]], 'cols' => ["$objectName.id"], 'where' => [
            				['col' => 'tukos.id', 'opr' => '<', 'values' => 0], 'tukos.object' => $objectName, ['col' => 'updated', 'opr' => '<', 'values' => $deleteBefore]]
            		]);
            		$stmt = $store->query("DELETE tukos, $objectName FROM tukos INNER JOIN $objectName ON tukos.id = - $objectName.id WHERE tukos.id < 0 AND tukos.object = '$objectName' AND tukos.updated < '$deleteBefore'");
            		$countDeleted = $stmt->rowCount();
            		$orphanIdsToDelete = $store->query("SELECT id FROM $objectName WHERE NOT EXISTS (SELECT NULL from tukos WHERE tukos.id = $objectName.id)")->fetchAll(\PDO::FETCH_COLUMN, 0);
            		if (!empty($orphanIdsToDelete)){
            		    $countOrphanDeleted = $store->query("DELETE FROM $objectName WHERE NOT EXISTS (SELECT NULL from tukos WHERE tukos.id = $objectName.id)")->rowCount();
            		    echo "\r\nFullClean - $countOrphanDeleted removed ids in table $objectName : they were not found in tukos: ". implode(', ', $orphanIdsToDelete);
            		    
            		}
            	}
            	$idsToDelete = array_merge($idsToDelete, $store->getAll(['table' => 'tukos', 'cols' => ['id'],
            			'where' => [['col' => 'id', 'opr' => '<', 'values' => 0], 'object' => $objectName, ['col' => 'updated' , 'opr' => '<', 'values' => $deleteBefore]]
            	]));
            	$stmt = $store->query("DELETE FROM tukos WHERE tukos.id < 0 AND tukos.object = '$objectName' AND tukos.updated < '$deleteBefore'");
            	$countDeleted += $stmt->rowCount();
                if ($countDeleted){
                	$totalDeleted += $countDeleted;
                	echo "\r\nFullClean - $countDeleted removed ids in table $objectName: " . implode(', ', array_column($idsToDelete, 'id'));
                }
            }
            if ($totalDeleted === 0){
               	echo "FullClean - no item had to be removed";
            }
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command aguments in FullClean: ', $e->getUsageMessage());
        }
    }
}
?>
