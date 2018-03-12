<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\Objects\Views\Overview\Models\Get as OverviewGetModel;
use TukosLib\TukosFramework as Tfk;

class Tab extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        //$this->actionModel  = new OverviewGetModel($this);
    }
    function response($query){
        $formContent         = $this->actionView->formContent((isset($this->view->customContentAtts['overview']) ? $this->view->customContentAtts['overview'] : []));
        return [
            'title'       => ucfirst($this->view->tr($this->view->objectName)) . ' - ' . ucfirst($this->view->tr('overview')),
            'closable'    => true,
            'focusOnOpen' => true,
            'style' => ['padding' => "0px"],
            'content'     => '',
            'formContent' => $formContent,
        ];
    }
}
?>
