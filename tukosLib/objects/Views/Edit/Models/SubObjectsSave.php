<?php
namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Objects\Views\Edit\Models\SubObjects;
use TukosLib\Objects\Views\Models\Delete as DeleteModel;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;

use TukosLib\TukosFramework as Tfk;

class SubObjectsSave extends SubObjects{

    public static function save($editModelSave, $valuesToSave, $newSavedId){
        $idsSaved = [];
        foreach ($valuesToSave as $widgetName => $values){
            $subObject = $editModelSave->view->subObjects[$widgetName];
            $subObjectModel = $editModelSave->objectsStore->objectModel($subObject['object']);
            $deleteValues = [];
            $idsDeleted = [];
            $saveSubObject = $editModelSave->objectsStore->objectViewModel($editModelSave->controller, 'Edit', 'Save', ['view' => $subObject['view'], 'model' => $subObject['model']]);
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
                    $idSaved = $saveSubObject->saveOne($rowValues, 'storeEditToObj');
                    if ($idSaved){
                        $idsSaved[] = $idSaved;
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
