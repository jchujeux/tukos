<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class CustomViewMore extends AbstractAction{
    function response($query){
        $response['defaultCustomViewContent'] = $this->view->user->getCustomView($this->view->objectName, 'edit');
        if (!empty($query['id'])){
            $itemCustomization = $this->view->model->getOne(['where' => ['id' => $query['id']], 'cols' => ['custom']], ['custom' => ['edit']]);
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
