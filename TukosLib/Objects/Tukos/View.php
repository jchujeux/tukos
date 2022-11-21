<?php
namespace TukosLib\Objects\Tukos;

use TukosLib\Objects\AbstractView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $customDataWidgets = [
            'name' => ['atts' => ['edit' => ['style' => ['width' => '30em']]]],
            'object' => ViewUtils::textBox($this, 'Object', [
                'objToOverview' => ['translate' => ['tr' => ['class' => $this]]],
            ])
        ];
        $this->customize($customDataWidgets);
        $this->customContentAtts = ['overview' => ['widgetsDescription' => ['overview' => ['atts' => ['freezeWidth' => false]]]]];
    }
    function gridCols(){
    	return array_merge(parent::gridCols(), ['object']);
    }
    function allowedGetCols($ignoreExceptions = []){
    	return array_merge(parent::allowedGetCols($ignoreExceptions), ['object']);
    }
}
?>
