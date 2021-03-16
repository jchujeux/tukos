<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Objects\Directory;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class CheckOrphanIds {

    function __construct($parameters){ 
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        $configStore  = Tfk::$registry->get('configStore');
        $objectsStore = Tfk::$registry->get('objectsStore');
        try{
            $options = new \Zend_Console_Getopt(
                ['app-s'		=> 'tukos application name (not needed in interactive mode)',
                 'db-s'		    => 'tukos application database name (not needed in interactive mode)',
                 'class=s'      => 'this class name',
                 'parentid-s'   => 'parent id (optional)',
                 'clean-s'      => 'set removed orphan ids to null'
                ]);
            $ids     = [];
            $idCols  = [];
            $objectsToConsider = Directory::getNativeObjs();
            foreach ($objectsToConsider as $objectName){
                if ($store->query("SELECT EXISTS(SELECT 1 FROM tukos WHERE object = '$objectName')")->fetch()[0]){
                    $presentObjects[] = $objectName;
                    $model = $objectsStore->objectModel($objectName);
                    $modelIdCols = $model->idCols;
                    $cols = $modelIdCols; $cols[] = 'id';
                    $modelIds = $model->getAll(['where' => [['col' => 'id', 'opr' => '>', 'values' => 0]], 'cols' => $cols, 'union' => false]);
                    $ids = array_merge($ids, array_column($modelIds, 'id'));
                    foreach($modelIdCols as $col){
                        $idCols = array_unique(array_merge($idCols, array_filter(array_column($modelIds, $col))));
                    }
                }
            }
            if (($store->dbName !== $configStore->dbName)){
                $configIds = $configStore->query("SELECT id from tukos WHERE id > 0")->fetchAll(\PDO::FETCH_COLUMN, 0);
                $ids = array_unique(array_merge($ids, $configIds));
            }
            $orphanIds = array_diff($idCols, $ids);
            if ($orphanIds){
                $output = 'the following ids are orphan: ' . implode(', ', $orphanIds) . '<br>They are referenced in the following objects:';
                foreach ($presentObjects as $objectName){
                    $model = $objectsStore->objectModel($objectName);
                    $modelIdCols = $model->idCols;
                    $where = [];
                    $or = null;
                    foreach ($modelIdCols as $col){
                        $where[] = ['col' => $col, 'opr' => 'IN', 'values' => $orphanIds, 'or' => $or];
                        $or = true;
                    }
                    $modelIds = $model->getAll(['where' => [['col' => 'id', 'opr' => '>', 'values' => 0], $where], 'cols' => ['id'], 'union' => false]);
                    if ($modelIds){
                        $output .= '<br>' . $objectName . ': ' . implode(', ', array_column($modelIds, 'id'));
                    }
                }
                if ($options->clean && strtolower($options->clean) === 'yes'){
                    $tukosModel = Tfk::$registry->get('tukosModel');
                    $tukosIdCols = array_keys($tukosModel->idColsObjects);
                    $presentOrphanIds = $store->query("SELECT -id FROM tukos WHERE -id in (" . implode(',', $orphanIds). ")")->fetchAll(\PDO::FETCH_COLUMN, 0);
                    $absentOrphanIds = array_diff($orphanIds, $presentOrphanIds);
                    if (!empty($absentOrphanIds)){
                        $absentOrphanIdsString = implode(',', $absentOrphanIds);
                        $output .= "<br>The following orphan ids were eliminated: $absentOrphanIdsString";
                        foreach($tukosIdCols as $idCol){                    
                            $stmt = $store->query("UPDATE tukos SET $idCol = NULL WHERE $idCol IN ($absentOrphanIdsString)");
                        };
                        $processedTables = [];
                        foreach($presentObjects as $objectName){
                            $model = $objectsStore->objectModel($objectName);
                            $tableName = $model->tableName;
                            if (!Utl::getItem($tableName, $processedTables)){
                                $tableIdCols = array_diff($model->idCols, $tukosIdCols, ['id']);
                                foreach ($tableIdCols as $idCol){
                                    $stmt = $store->query("UPDATE $tableName SET $idCol = NULL WHERE $idCol IN ($absentOrphanIdsString)");
                                }
                                $processedTables[$tableName] = true;
                            }
                        }
                    }else{
                        $output .= "<br>No orphan ids were eliminated";
                    }
                }else{
                    $output .= "<br>No orphan ids were eliminated (clean !== 'yes')";
                }
            }else{
                $output = '<br>No orphan ids found';
            }
            echo $output;
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command arguments in CheckOrphanIds: ', $e->getUsageMessage());
        }
    }
}
?>
