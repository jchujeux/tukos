<?php
/**
 *
 * Provide methods to deal with parent - child methods for object items
 */
namespace TukosLib\Objects\Admin\Users\CustomViews;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

trait UserCustomization {

    public function getCustomization ($keys){
        $objectName = array_shift($keys);
        $viewName   = array_shift($keys);
        $paneMode = array_shift($keys);
        $objectsStore = Tfk::$registry->get('objectsStore');
        $customViewsModel = $objectsStore->objectModel('customviews');
        return $customViewsModel->getOne(
            ['where' => ['parentid' => $this->id(), 'vobject' => $objectName, 'view' => $viewName, 'panemode' => $paneMode], 'cols' => ['customization']], ['customization' => $keys]
        )['customization'];
    }
    
    public function updateCustomization($newValue){
        $objectName = array_keys($newValue)[0];
        $viewName   = array_keys($newValue[$objectName])[0];
        $paneMode = array_keys($newValue[$objectName][$viewName][0]);
        $objectsStore = Tfk::$registry->get('objectsStore');
        $customViewsModel = $objectsStore->objectModel('customviews');
        return $customViewsModel->updateOne(
            ['customization' => $newValue[$objectName][$viewName], 'parentid' => $this->id(), 'vobject' => $objectName, 'view' => $viewName, 'panemode' => $paneMode], 
            ['where' => ['parentid' => $this->id(), 'vobject' => $objectName, 'view' => $viewName, 'panemode' => $paneMode]], 
            true, true
        );
    }

    public function deleteCustomization($valuesToDelete){
        $objectName = array_keys($valuesToDelete)[0];
        $viewName   = array_keys($valuesToDelete[$objectName])[0];
        $paneMode = array_keys($newValue[$objectName][$viewName][0]);
        $objectsStore = Tfk::$registry->get('objectsStore');
        $customViewsModel = $objectsStore->objectModel('customviews');
        if ($valuesToDelete[$objectName][$viewName] === true){
            $customViewsModel->delete(['where' => ['parentid' => $this->id(), 'vobject' => $objectName, 'view' => $viewName, 'panemode' => $paneMode]]);
            return [];
        }else{
            $customization =  $customViewsModel->getOne(
                ['where' => ['parentid' => $this->id(), 'vobject' => $objectName, 'view' => $viewName, 'panemode' => $paneMode], 'cols' => ['customization']], ['customization' => []]
            )['customization'];
            Utl::drillDownDelete($customization, $valuesToDelete[$objectName][$viewName][$paneMode]);
            $customViewsModel->updateOne(
                ['customization' => json_encode($customization)], 
                ['where' => ['parentid' => $this->id(), 'vobject' => $objectName, 'view' => $viewName, 'panemode' => $paneMode]]
            );
            return $customization;
        }
    }
}
?>
