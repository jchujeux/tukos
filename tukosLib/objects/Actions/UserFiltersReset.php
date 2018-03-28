<?php

namespace TukosLib\Objects\Actions;


trait UserFiltersReset{
    function response($query){
/*
    	$viewCustomization = $this->dialogue->getValues();
        if (isset($this->controller->customViewId)){
            $this->objectsStore->objectModel('customviews')->updateOne(
                ['vobject' => $this->objectName, 'view' => $this->request['view'], 'panemode' => $this->paneMode, 'customization' => $viewCustomization], 
                ['where' => ['id' => $this->controller->customViewId]], 
                true, true
            );
        }else{
			$this->user->updateCustomView($this->objectName, $this->request['view'], $this->paneMode, $viewCustomization);
        }
*/
        $this->user->updateCustomView($this->objectName, $this->request['view'], $this->paneMode, $this->dialogue->getValues());
        return parent::response($query);
    }
}
?>
