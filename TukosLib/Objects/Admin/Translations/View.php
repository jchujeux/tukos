<?php
namespace TukosLib\Objects\Admin\Translations;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator = null){
        ObjectTranslator::__construct($objectName, $translator);
        $this->objectName = $objectName;
        $this->model = Tfk::$registry->get('objectsStore')->objectModel($this->objectName, $this->tr);
        $this->user  = Tfk::$registry->get('user');
        $translationsEditor = $this->user->translationsEditor();
        $this->sendOnSave = $this->sendOnDelete = [];
        $this->mustGetCols = ['id'];
        $this->tr = Tfk::$tr;//so that admin in setname is translated as administration, not administrator
        $tr = $this->tr;
        $customizableAtts = [$tr('editorType') => ['att' => 'editorType', 'type' => 'StoreSelect', 'name' => $tr('editorType'), 'storeArgs' => ['data' => [['id' => 'normal', 'name' => $tr('normal')], ['id' =>  'simple', 'name' =>  $tr('simple')], ['id' =>  'basic', 'name' =>  $tr('basic')]]]]];
        $this->dataWidgets = [
            'id' => ViewUtils::textBox($this, 'Id', [
                    'atts' => [
                        'edit' =>  ['disabled' => true, 'style' => ['width' => ['3em']]],
                        'storeedit' => ['width' => 110, 'onClickFilter' => ['id'], 'renderExpando' => true, 'formatter' => '', 'renderCell' => ''],
                        'overview'  => ['width' => 110, 'onClickFilter' => ['id']],
                    ]
                ]
            ), 
            'name'      => ViewUtils::textArea($this, 'Translation key', ['atts' => ['storeedit' => ['onClickFilter' => ['id']], 'overview'  => ['onClickFilter' => ['id']]]]),
        	'setname'  => ViewUtils::storeSelect('setName', $this, 'Translation set'),
            'en_us'    => ViewUtils::lazyEditor($this, 'English', ['atts' => ['edit' => [
                'height' => '550px', 'editorType' => $translationsEditor,
                'customizableAtts' => $customizableAtts
            ]]]),
            'fr_fr'    => ViewUtils::lazyEditor($this, 'fr_fr', ['atts' => ['edit' => [
                'height' => '550px', 'editorType' => $translationsEditor,
                'customizableAtts' => $customizableAtts
            ]]]),
            'es_es'    => ViewUtils::lazyEditor($this, 'Spanish', ['atts' => ['edit' => [
                'height' => '550px', 'editorType' => $translationsEditor,
                'customizableAtts' => $customizableAtts
            ]]])
        ];
        $this->defaultDataWidgetsElts = array_keys($this->dataWidgets);
        $this->responseContent = null;
        $this->subObjects = [];
        $this->customContentAtts = ['overview' => ['widgetsDescription' => ['overview' => ['atts' => ['sort' => [['property' => 'id', 'descending' => true]]]]]]];
        $this->jsonColsPathsView = [];
        $this->customize([]);
    }
    function gridCols(){// cols which are rendered on the overview grid & on subobjects grids
        return array_keys($this->dataWidgets);
    }
    function tabEditTitle ($values){
        return (empty($values['id'])
            ? $this->tr($this->objectName) . ' (' . $this->tr('new') . ')'
            : Utl::concat(Utl::getItems($this->model->extendedNameCols, $values),' ', 25)  . ' (' . ucfirst($this->tr($this->objectName)) . '  '  . $values['id'] . ')');
    }
}
?>
