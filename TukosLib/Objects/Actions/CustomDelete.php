<?php
namespace TukosLib\Objects\Actions;

use TukosLib\Objects\Actions\Edit\Tab;
use TukosLib\Utils\Utilities as Utl;

trait CustomDelete{
    function response($query){
        $customizationToDelete = $this->dialogue->getValues();        
        if (!empty($toDelete = Utl::extractItem('itemCustom', $customizationToDelete))){
            $remainingItemCustomization = $this->model->deleteItemCustomization(['id' => $query['id']], ['edit' => [strtolower($this->paneMode) => $toDelete['items']]]);
            $response['itemCustom'] = isset($remainingItemCustomization['edit'][$this->paneMode]) ? $remainingItemCustomization['edit'][$this->paneMode] : [];
        }
        $customViewsModel = $this->objectsStore->objectModel('customviews');
        foreach($customizationToDelete as $tukosOrUserOrItem => $toDelete){
            $response[$tukosOrUserOrItem] = $customViewsModel->deleteCustomization(['id' => $toDelete['viewId']], $toDelete['items']);
        }
        return array_merge( parent::response($query), ['customContent' => $response]);
    }
}
?>
