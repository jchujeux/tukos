<?php
namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Objects\Views\Edit\Models\SubObjects;
use TukosLib\Objects\Views\Models\Delete as DeleteModel;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;

use TukosLib\TukosFramework as Tfk;

class SubObjectsSave extends SubObjects{

    public static function save($editModelSave, $valuesToSave, $newSavedId){
        $idsSaved = []; $newRowsMapping = []; $newRowPrefixArray = [];
        foreach ($valuesToSave as $widgetName => $values){
            $subObject = $editModelSave->view->subObjects[$widgetName];
            $dataWidgets = Utl::array_merge_recursive_replace($subObject['view']->dataWidgets, Utl::getItem('colsDescription', $subObject['atts'], []));
            $subObjectModel = $editModelSave->objectsStore->objectModel($subObject['object']);
            $deleteValues = []; $idsDeleted = []; $widgetsMapping = [];
            $saveSubObject = $editModelSave->objectsStore->objectViewModel($editModelSave->controller, 'Edit', 'Save', ['view' => $subObject['view'], 'model' => $subObject['model']]);
            foreach ($dataWidgets as $colName => $widget){
                if ($widget['type'] === 'ObjectSelect' && $gridName = Utl::drillDown($widget['atts'], ['storeedit', 'editorArgs', 'storeArgs', 'storeDgrid'], null)){
                    $widgetsMapping[$colName] = $gridName;
                }                
            }
            if ($newRowPrefix = Utl::getItem('newRowPrefix', $subObject['atts'])){
                $newRowPrefixArray[$widgetName] = $newRowPrefix;
            }
            foreach ($values as $key => $rowValues){
                if (!empty($rowValues['~delete'])){
                    $deleteValues[] = $rowValues;
                    unset ($rowValues[$key]);
                }else{// update existing id, or insert new one
                    if ($newSavedId){
                        $filteredIdCol = array_search('@id', $subObject['filters']);
                        if ($filteredIdCol && empty($rowValues[$filteredIdCol])){
                            $rowValues[$filteredIdCol] = $newSavedId;
                        }
                    }
                    if ($newRowPrefix && (strpos(Utl::getItem('id', $rowValues, ''), $newRowPrefix) === 0)){
                        $newId = Utl::extractItem('id', $rowValues);
                    }else{
                        $newId = '';
                    }
                    foreach ($widgetsMapping as $colName => $widgetGridName){
                        if (($prefix = Utl::getItem($widgetGridName, $newRowPrefixArray, false)) && strpos(Utl::getItem($colName, $rowValues, ''), $prefix) === 0){
                            $rowValues[$colName] = Utl::drillDown($newRowsMapping, [$widgetGridName, $rowValues[$colName]], null);
                        }
                    }
                    $idSaved = $saveSubObject->saveOne($rowValues, 'storeEditToObj');
                    if ($idSaved){
                        $idsSaved[] = $idSaved;
                        if ($newId){
                            if (!isset($newRowsMapping[$widgetName])){
                                $newRowsMapping[$widgetName] = [];
                            }
                            $newRowsMapping[$widgetName][$newId] = $idSaved;
                        }
                    }
                }

            }
            if (! empty($deleteValues)){
                $deleteSubObject = new DeleteModel($editModelSave->controller,  ['view' => $subObject['view'], 'model' => $subObjectModel]);
                $idsDeleted = $deleteSubObject->deleteMultiple($deleteValues);
            }
        }
        return ['saved' => $idsSaved, 'deleted' => $idsDeleted];
    }



}
?>
