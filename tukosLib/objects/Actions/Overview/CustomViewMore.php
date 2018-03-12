<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class CustomViewMore extends AbstractAction{
    function response($query){
        $response['defaultCustomViewContent'] = $this->view->user->getCustomView($this->view->objectName, 'overview');
        return $response;
    }
}
?>
