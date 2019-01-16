<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\Edit\Tab;
use TukosLib\TukosFramework as Tfk;

class CustomDelete extends Tab{
    function response($query){
        $customizationToDelete = $this->dialogue->getValues();        

        $customViewsModel = $this->objectsStore->objectModel('customviews');
        if (!empty($customizationToDelete['defaultCustomView'])){
            $toDelete = $customizationToDelete['defaultCustomView'];
            $response['defaultCustomView'] = $customViewsModel->deleteCustomization(['id' => $toDelete['viewId']], $toDelete['items']);
        }
        if (!empty($customizationToDelete['itemCustomView'])){
            $toDelete = $customizationToDelete['itemCustomView'];
            $response['itemCustomView'] = $customViewsModel->deleteCustomization(['id' => $toDelete['viewId']], $toDelete['items']);
        }
        if (!empty($customizationToDelete['itemCustom'])){
            $toDelete = $customizationToDelete['itemCustom'];
            $remainingItemCustomization = $this->model->deleteItemCustomization(['id' => $query['id']], ['edit' => [strtolower($this->paneMode) => $toDelete['items']]]);
            $response['itemCustom'] = isset($remainingItemCustomization['edit'][$this->paneMode]) ? $remainingItemCustomization['edit'][$this->paneMode] : [];
        }
        return array_merge( parent::response($query), ['customContent' => $response]);
    }
}
?>
