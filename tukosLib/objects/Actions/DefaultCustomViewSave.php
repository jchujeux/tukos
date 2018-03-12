<?php

namespace TukosLib\Objects\Actions;


trait DefaultCustomViewSave{
    function response($query){
        $viewCustomization = $this->dialogue->getValues();
        if (isset($this->controller->customViewId)){
            $this->objectsStore->objectModel('customviews')->updateOne(
                ['vobject' => $this->objectName, 'view' => $this->request['view'], 'customization' => $viewCustomization], 
                ['where' => ['id' => $this->controller->customViewId]], 
                true, true
            );
        }else{
			$this->user->updateCustomView($this->objectName, $this->request['view'], $viewCustomization);
        }
        return parent::response($query);
    }
}
?>
