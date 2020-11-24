<?php
namespace TukosLib\Objects\Tukos;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [];
        parent::__construct($objectName, $translator, 'tukos', ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects()], [], $colsDefinition, [], ['object'], []);
    }
    function restore($where){
        $idsAndObjects = $this->getAll(['where' => $where, 'eliminateditems' => true, 'cols' => ['id', 'object']]);
        //$this->updateItems([], ['where' => [['col' => 'id', 'opr' => 'in', 'values' => array_column($idsAndObjects, 'id')]], 'set' => ['id' => '-id', 'updated' => "'" . date('Y-m-d H:i:s') . "'", 'updator' => $this->user->id()]]);
        $idsToRestore = array_column($idsAndObjects, 'id');
        $idsToCheck = [];
        foreach($idsToRestore as $id){
            $idsToCheck[] = - $id;
        }
        $idsToExclude = array_column($this->getAll(['where' => [['col' => 'id', 'opr' => 'in', 'values' => $idsToCheck]], 'cols' => ['id']]), 'id');
        if (!empty($idsToExclude)){
            $idsToRestore = array_diff($idsToRestore, $idsToExclude);
            Feedback::add(["{$this->tr('ExistingIdsCouldNotBeRestored')}: "  => $idsToExclude]);
        }
        $this->store->update([], ['table' => 'tukos', 'where' => [['col' => 'id', 'opr' => 'in', 'values' => $idsToRestore]], 
                                  'set' => ['id' => '-id', 'updated' => "'" . date('Y-m-d H:i:s') . "'", 'updator' => $this->user->id()]]);
        $objectsStore = Tfk::$registry->get('objectsStore');
        $idsByObject = Utl::toAssociativeGrouped($idsAndObjects, 'object', true);
        foreach ($idsByObject as $objectName => $ids){
            $idsToConsider = array_diff($ids, $idsToExclude);
            if (!empty($idsToConsider)){
                $model = $objectsStore->objectModel($objectName);
                if (property_exists($model, 'processInsertForBulk') && method_exists($model, $processInsertForBulk = $model->processInsertForBulk)){
                    $positiveIds = [];
                    foreach($idsToConsider as $id){
                        $positiveIds[] = -$id;
                    }
                    $restoredItems = $model->getAll(['where' => [['col' => 'id', 'opr' => 'in', 'values' => $positiveIds]], 'cols' => property_exists($this, 'additionalColsForBulkDelete') ? array_merge(['id'], $this->additionalColsForBulkDelete) : ['id']]);
                    if (method_exists($model, 'bulkPreProcess')){
                        $model->bulkPreProcess();
                    }
                    foreach ($restoredItems as $item){
                        $this->$processInsertForBulk($item);
                    }
                    if (method_exists($model, 'bulkPostProcess')){
                        $model->bulkPostProcess();
                    }
                }
                Feedback::add(["{$this->tr('IdsRestoredforobject')} {$this->tr($objectName)}: "  => $idsToConsider]);
            }
        }
    }
}
?>
