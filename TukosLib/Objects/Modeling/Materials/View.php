<?php
namespace TukosLib\Objects\Modeling\Materials;

use TukosLib\Objects\AbstractView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $customDataWidgets = [
            'name' => ['atts' => ['edit' => ['style' => ['width' => '40em']]]],
            'comments' => ['atts' => ['edit' => ['width' => '600px', 'height' => '100px']]],
            'description' => ViewUtils::JsonGrid($this, 'MaterialDescription', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'rheology' => ViewUtils::StoreSelect('rheology', $this, 'Rheology', [true, 'ucfirst', false, false, false], ['atts' => ['storeedit' => ['width' => 200, 'editorArgs' => ['style' => ['width' => '10em']]]]]),
                'properties'=> ViewUtils::textArea($this, 'Properties', ['atts' => ['storeedit' => ['editorArgs' => ['style' => ['width' => '100em']]]]]),
            ], ['atts' => ['edit' => ['maxHeight' => '300px']]]),
        ];
        $this->customize($customDataWidgets);
    }    
}
?>
