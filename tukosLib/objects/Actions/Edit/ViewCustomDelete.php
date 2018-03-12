<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class ViewCustomDelete extends AbstractAction{
    function response($query){
        $customizationToProcess = $this->dialogue->getValues();        
        if ($customizationToProcess['view']){
            $response['view'] = $this->view->user->deleteCustomization([$this->view->objectName => ['edit' => $customizationToProcess['view']]]);
        }else{
            $response['view'] = $this->view->user->getCustomView($this->view->objectName, 'edit');
        }
        if (!empty($query['id']) && !empty($customizationToProcess['item'])){
            $remainingItemCustomization = $this->view->model->deleteItemCustomization(['id' => $query['id']], ['edit' => $customizationToProcess['item']]);
            $response['item'] = isset($remainingItemCustomization['edit']) ? $remainingItemCustomization['edit'] : [];
        }else{
            $response['item'] = $this->view->model->getItemCustomization(['id' => $query['id']], ['edit'], true);
        }  
        return $response;
    }
}
?>
