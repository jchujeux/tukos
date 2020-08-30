<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class CheckOrphanIds {

    function __construct($parameters){ 
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');
        try{
            $options = new \Zend_Console_Getopt(
                ['app-s'		=> 'tukos application name (not needed in interactive mode)',
                 'db-s'		    => 'tukos application database name (not needed in interactive mode)',
                 'class=s'      => 'this class name',
                 'parentid-s'   => 'parent id (optional)',
                ]);
            $ids     = [];
            $idCols  = [];
            $objectsToConsider = Directory::getNativeObjs();
            foreach ($objectsToConsider as $objectName){
                if ($exists = $store->query("SELECT EXISTS(SELECT 1 FROM tukos WHERE object = '$objectName')")->fetch()[0]){
                    $model = $objectsStore->objectModel($objectName);
                    $modelIdCols = $model->idCols;
                    $cols = $modelIdCols; $cols[] = 'id';
                    $modelIds = $model->getAll(['where' => [['col' => 'id', 'opr' => '>', 'values' => 0]], 'cols' => $cols]);
                    $ids = array_merge($ids, array_column($modelIds, 'id'));
                    foreach($modelIdCols as $col){
                        $idCols = array_unique(array_merge($idCols, array_filter(array_column($modelIds, $col))));
                    }
                }
            }
            $orphanIds = array_diff($idCols, $ids);
            if ($orphanIds){
                $objValue  = ['name'            => 'CheckOrphanIds', 
                              'parentid'        => $options->parentid    ? $options->parentid    : $user->id(), 
                              'datehealthcheck' => date('Y-m-d H:i:s'),
                              'comments'        => 'the following ids are orphan: ' . implode(', ', $orphanIds) . '<br>They are referenced in the following objects:',
                             ];
                foreach ($objectsToConsider as $objectName){
                    if ($store->tableExists($objectName)){
                        $model = $objectsStore->objectModel($objectName);
                        $modelIdCols = $model->idCols;
                        $where = [];
                        $or = null;
                        foreach ($modelIdCols as $col){
                            $where[] = ['col' => $col, 'opr' => 'IN', 'values' => $orphanIds, 'or' => $or];
                            $or = true;
                        }
                        $modelIds = $model->getAll(['where' => [['col' => 'id', 'opr' => '>', 'values' => 0], $where], 'cols' => ['id']]);
                        if ($modelIds){
                            $objValue['comments'] .= '<br>' . $objectName . ': ' . implode(', ', array_column($modelIds, 'id'));
                        }
                    }
                }
                echo $objValue['comments'];
            }
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command arguments in CheckOrphanIds: ', $e->getUsageMessage());
        }
    }
}
?>
