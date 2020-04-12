<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class CustomViewMore extends AbstractAction{
    function response($query){
        $customViewId = $this->user->customViewId($this->objectName, 'overview', $this->paneMode, 'user');
        $tukosViewId = $this->user->userName() === 'tukos' ? 0 : $this->user->customViewId($this->objectName, 'overview', $this->paneMode, 'tukos');
        $response['tukosCustomViewContent'] = empty($tukosViewId) ? [] : $this->objectsStore->objectModel('customviews')->getOne(['where' => ['id' => $tukosViewId], 'cols' => ['customization']], ['customization' => []])['customization'];
        $response['defaultCustomViewContent'] = empty($customViewId) ? [] : $this->objectsStore->objectModel('customviews')->getOne(['where' => ['id' => $customViewId], 'cols' => ['customization']], ['customization' => []])['customization'];
        //$response['defaultCustomViewContent'] = $this->user->getCustomView($this->objectName, 'overview', $this->paneMode);
        return $response;
    }
}
?>
