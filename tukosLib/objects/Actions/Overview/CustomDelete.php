<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\Overview\Tab;
use TukosLib\TukosFramework as Tfk;

class CustomDelete extends Tab{
    function response($query){
        $customizationToDelete = $this->dialogue->getValues();        

        $customViewsModel = $this->objectsStore->objectModel('customviews');
        if (!empty($customizationToDelete['defaultCustomView'])){
            $toDelete = $customizationToDelete['defaultCustomView'];
            $response['defaultCustomView'] = $customViewsModel->deleteCustomization(['id' => $toDelete['viewId']], $toDelete['items']);
        }
        return array_merge( parent::response($query), ['customContent' => $response]);
    }
}
?>
