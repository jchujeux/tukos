<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Store\Store;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class CleanTukosConfig {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $configStore  = new Store(array_merge($appConfig->dataSource, ['dbname' => 'tukosconfig']));
        try{
            $objectsToConsider = array_intersect(Directory::getNativeObjs(), $configStore->tableList());
            $droppedTables = [];
            $idsToDelete = $configStore->getAll(['table' => 'tukos', 'cols' => ['id'],
                'where' => [['col' => 'id', 'opr' => '<', 'values' => 0], ['col' => 'id' , 'opr' => '>', 'values' => 10000]]
            ]);
            $stmt = $configStore->query("DELETE FROM tukos WHERE tukos.id < 0 AND tukos.id > 10000");
            $countDeleted = $stmt->rowCount();
            if ($countDeleted){
                echo "\r\nCleanTukosConfig - $countDeleted removed ids in table tukos: " . implode(', ', array_column($idsToDelete, 'id'));
            }
            foreach ($objectsToConsider as $objectName){
                if ($nonEmpty = $configStore->query("SELECT 1 as NON_EMPTY from $objectName LIMIT 1")->fetch()){
                    $idsToDelete = [];
                	$countDeleted = 0;
            		$idsToDelete = $configStore->query("SELECT id FROM $objectName WHERE NOT EXISTS (SELECT NULL from tukos WHERE tukos.id = $objectName.id)")->fetchAll(\PDO::FETCH_COLUMN, 0);
            		if (!empty($idsToDelete)){
            		    $countDeleted = $configStore->query("DELETE FROM $objectName WHERE NOT EXISTS (SELECT NULL from tukos WHERE tukos.id = $objectName.id)")->rowCount();
            		    echo "\r\nCleanTukosConfig - $countDeleted removed ids in table $objectName : they were not found in tukos: ". implode(', ', $idsToDelete);
            		    $nonEmpty = $configStore->query("SELECT 1 as NON_EMPTY from $objectName LIMIT 1")->fetch();            		}
                }
                if (!$nonEmpty){
                    $configStore->query("drop table $objectName");
                    $droppedTables[] = $objectName;
                }
            }
            if (!empty($droppedTables)){
                echo "\r\nCleanTukosConfig - Empty tables dropped: " . implode(', ', $droppedTables);
            }
        }catch(\Exception $e){
            Tfk::debug_mode('log', 'an exception occured in CleanTukosConfig: ', $e->getMessage());
        }
    }
}
?>
