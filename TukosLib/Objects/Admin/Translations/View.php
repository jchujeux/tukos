<?php
namespace TukosLib\Objects\Admin\Translations;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator = null){
        ObjectTranslator::__construct($objectName, $translator);
        $this->objectName = $objectName;
        $this->model = Tfk::$registry->get('objectsStore')->objectModel($this->objectName, $this->tr);
        $this->user  = Tfk::$registry->get('user');
        $this->sendOnSave = $this->sendOnDelete = [];
        $this->mustGetCols = ['id'];

        $this->dataWidgets = [
            'id' => ViewUtils::textBox($this, 'Id', [
                    'atts' => [
                        'edit' =>  ['disabled' => true, 'style' => ['width' => ['9em']]],
                        'storeedit' => ['width' => 110, 'onClickFilter' => ['id'], 'renderExpando' => true, 'formatter' => '', 'renderCell' => ''],
                        'overview'  => ['width' => 110, 'onClickFilter' => ['id']],
                    ]
                ]
            ), 
            'name'      => ViewUtils::textArea($this, 'Translation key', ['storeedit' => ['onClickFilter' => ['id']], 'overview'  => ['onClickFilter' => ['id']]]),
        	'setname'  => ViewUtils::storeSelect('setName', $this, 'Translation set'),
            'en_us'    => ViewUtils::textArea($this, 'English'),
            'fr_fr'    => ViewUtils::textArea($this, 'French'),
            'es_es'    => ViewUtils::textArea($this, 'Spanish'),
        ];
        $this->defaultDataWidgetsElts = array_keys($this->dataWidgets);
        $this->responseContent = null;
        $this->subObjects = [];
        $this->customContentAtts = ['overview' => ['widgetsDescription' => ['overview' => ['atts' => ['sort' => [['property' => 'id', 'descending' => true]]]]]]];
        $this->customize([]);
    }
    function gridCols(){// cols which are rendered on the overview grid & on subobjects grids
        return array_keys($this->dataWidgets);
    }
}
?>
