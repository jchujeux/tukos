<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Tab extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->actionViewModel  = $controller->objectsStore->objectViewModel($controller, 'Edit', 'Get');
    }
    function response($query){
        $formContent         = $this->actionView->formContent((isset($this->view->customContentAtts['edit']) ? $this->view->customContentAtts['edit'] : ($this->request['object'] === 'backoffice' ? $query : [])));
        $this->actionViewModel->respond($formContent, $query);
        return [
            'title'       => $this->view->tabEditTitle($item = $formContent['data']['value']),
            'contentId' => Utl::getItem('id', $item),
            'contentName' => Utl::getItem('name', $item),
            'closable'    => true,
            'focusOnOpen' => true,
            'style'       => 'padding: 0px;',
            'content'     => '',
            'formContent' => $formContent,
            'messages' => method_exists($this->view, 'getToTranslate') ? Tfk::$registry->get('translatorsStore')->getTranslations($this->view->getToTranslate(), $this->objectName) : []
        ];
    }
}
?>
