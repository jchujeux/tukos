<?php

namespace TukosLib\Objects\Admin\JsTesting;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){

        parent::__construct($objectName, $translator, 'Owner', 'Description');

        $customDataWidgets = [
            'name' => ['atts' => ['edit' => ['style' => ['width' => '40em']]]],
            'tukosmodulepath'=> ViewUtils::textArea($this, 'TukosModulePath', ['atts' => ['edit' =>  ['style' => ['width' => '40em']]]]),
            'function'=> ViewUtils::textBox($this, 'Function', ['atts' => ['edit' =>  ['style' => ['width' => '15em']]]]),
            'parameters'=> ViewUtils::textArea($this, 'Parameters', ['atts' => ['edit' =>  ['style' => ['width' => '40em']]]]),
            'comments' => ['atts' => ['edit' => ['height' => '250px']]],
            'outcome' => ViewUtils::lazyEditor($this, 'LastOutcome', ['atts' => ['edit' => ['height' => '310px'], 'overview' => ['hidden' => true]]]),
        ];
        $this->customize($customDataWidgets);
    }    
    
}
?>
