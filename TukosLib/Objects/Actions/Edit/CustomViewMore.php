<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class CustomViewMore extends AbstractAction{
    function response($query){
        $paneMode = strtolower($this->paneMode);
        $customViewId = $this->user->customViewId($this->objectName, 'edit', $this->paneMode, 'user');
        $tukosViewId = $this->user->userName() === 'tukos' ? 0 : $this->user->customViewId($this->objectName, 'edit', $this->paneMode, 'tukos');
        $response['tukosCustomViewContent'] = empty($tukosViewId) ? [] : $this->objectsStore->objectModel('customviews')->getOne(['where' => ['id' => $tukosViewId], 'cols' => ['customization']], ['customization' => []])['customization'];
        $response['defaultCustomViewContent'] = empty($customViewId) ? [] : $this->objectsStore->objectModel('customviews')->getOne(['where' => ['id' => $customViewId], 'cols' => ['customization']], ['customization' => []])['customization'];
        if (!empty($query['id'])){
            $itemCustomization = $this->view->model->getOne(['where' => ['id' => $query['id']], 'cols' => ['custom']], ['custom' => ['edit', $paneMode]]);
            if (!empty($itemCustomization['custom'])){
                $response['itemCustomContent'] = $itemCustomization['custom'];
                if (!empty($response['itemCustomContent']['itemcustomviewid'])){
                    $response['itemCustomViewContent'] = $this->objectsStore->objectModel('customviews')->getOne(['where' => ['id' => $response['itemCustomContent']['itemcustomviewid']], 'cols' => ['customization']], ['customization' => []])['customization'];
                }
            }
        }
        return $response;
    }
}
?>
