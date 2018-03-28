<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class CustomViewMore extends AbstractAction{
    function response($query){
        $response['defaultCustomViewContent'] = $this->user->getCustomView($this->objectName, 'overview', $this->paneMode);
        return $response;
    }
}
?>
