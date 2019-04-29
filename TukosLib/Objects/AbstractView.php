<?php
/**
 *
 * class for viewing methods and properties for the tukos model object
 */
namespace TukosLib\Objects;

use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\Utils\Widgets;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

abstract class AbstractView extends ObjectTranslator{

    public $_exceptionCols = ['edit' => [], 'grid' => [], 'get' => [], 'post' => []];

    function __construct($objectName, $translator, $parentWidgetTitle='Parent', $nameWidgetTitle='Name', $parentObjects = 'parentid'){
        parent::__construct($objectName, $translator);
        $this->objectName = $objectName;
        $objectsStore  = Tfk::$registry->get('objectsStore');
        $this->model = $objectsStore->objectModel($this->objectName, $this->tr);
        $this->user  = Tfk::$registry->get('user');
        $this->sendOnSave = $this->sendOnDelete = ['updated'];
        $this->mustGetCols = ['id', 'parentid', 'updated', 'permission', 'updator'];
        //$this->senderTargetWidget = 'comments';

        $this->dataWidgets = [
            'id' => ViewUtils::textBox($this, 'Id', [
                    'atts' => [
                        'edit' =>  ['readOnly' => true, 'style' => ['width' => '6em', 'backgroundColor' => 'WhiteSmoke']],
                        'storeedit' => ['width' => 60, 'onClickFilter' => ['id'], 'renderExpando' => true, 'formatter' => '', 'renderCell' => '', 'editOn' => 'dblClick'],
                        'overview'  => ['width' => 60, 'onClickFilter' => ['id']],
                    ]
                ]
            ), 
            'parentid'  => ViewUtils::objectSelectMulti($parentObjects, $this, $parentWidgetTitle),
            'name'      => ViewUtils::textBox($this, $nameWidgetTitle, [/*'atts' => ['edit' => ['style' => ['width' => '20em']]],*/ 'storeedit' => ['onClickFilter' => ['id']], 'overview'  => ['onClickFilter' => ['id']]]),
            'comments'  => ViewUtils::lazyEditor($this, 'CommentsDetails', ['atts' => ['edit' => ['height' => '400px']]]),
            'permission' => ViewUtils::storeSelect('permission', $this, 'Access Control', false, ['atts' => ['storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]]),
            'grade' => ViewUtils::storeSelect('grade', $this, 'Grade', true, ['atts' => ['storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]]),
            'contextid'  => [
                'type' => 'objectSelectDropDown', 
                'atts' => [
                    'edit' => [
                        'label' => $this->tr('Context'), 'style' => ['width' => '12em'], 'object' => 'contexts',
                        'dropDownWidget' => ['type' => 'storeTree', 'atts' => $this->user->contextTreeAtts($this->tr)],
                    ],
                    'storeedit'=>['editorArgs' => ['style' => ['width' => '8em']]],
                ], 
                'objToEdit' => ['translate' => ['tr' => ['class' => $this]]],                        
                'objToStoreEdit' => ['translate' => ['tr' => ['class' => $this]]],                        
                'objToOverview' => ['translate' => ['tr' => ['class' => $this]]],                        
            ],
            'updator'   => ViewUtils::objectSelect($this, 'Last edited by', 'users', ['atts' => ['edit' => ['placeHolder' => '', 'style' => ['width' => '10em', 'backgroundColor' => 'WhiteSmoke'], 'readOnly' => true], 'storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]]),
            'updated'   => ViewUtils::timeStampDataWidget($this, 'Last edit date', ['atts' => ['edit' => ['style' => ['backgroundColor' => 'WhiteSmoke'], 'readOnly' => true]]]),
            'creator'   => ViewUtils::objectSelect($this, 'Created by', 'users', ['atts' => ['edit' => ['placeHolder' => '', 'style' => ['width' => '10em', 'backgroundColor' => 'WhiteSmoke'], 'readOnly' => true], 'storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]]),
            'created'   => ViewUtils::timeStampDataWidget($this, 'Creation date', ['atts' => ['edit' => ['style' => ['backgroundColor' => 'WhiteSmoke'], 'readOnly' => true], 'storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]]),
            
        ];
        if ($this->user->rights() === 'SUPERADMIN'){
            $this->dataWidgets['configstatus'] = ViewUtils::storeSelect('configStatus', $this, 'Config Status', true, ['atts' => ['storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]]);
        }
        $this->defaultDataWidgetsElts = array_keys($this->dataWidgets);
        $this->responseContent = null;
        $this->subObjects = [];
        $this->customFormContent = [];
    }
    function customize($customDataWidgets=[], $subObjects=[], $exceptionCols=[], $jsonColsPathsView=[]){

        if ($customDataWidgets){
            $this->addedDataWidgetsElts = array_diff(array_keys($customDataWidgets), $this->defaultDataWidgetsElts, ['custom', 'worksheet', 'history']);
            $this->dataWidgets = Utl::array_merge_recursive_replace($this->dataWidgets, $customDataWidgets);
        }else{
            $this->addedDataWidgetsElts = [];
        }
        if ($subObjects){
            $this->subObjects = $subObjects;
        }
        $this->_exceptionCols = array_merge($this->_exceptionCols, $exceptionCols);
        $this->jsonColsPathsView     = $jsonColsPathsView;

        if (in_array('custom', $this->model->allCols)){
            $this->dataWidgets['custom'] = ['type' => 'textArea', 'atts' => ['edit' => ['label' => $this->tr('itemcustom')], 'storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]];
            $this->_exceptionCols['edit'][] = 'custom';
            $this->_exceptionCols['post'][] = 'custom';
            $this->_exceptionCols['get'][] = 'custom';
        }

        if (in_array('worksheet', $this->model->allCols)){
            $this->dataWidgets['worksheet'] = [
                'type' => 'sheetGrid', 
                'atts' => ['edit' => ['label' => $this->tr('Attached Worksheet'), 'hidden' => true, 'storeType' => 'MemoryTreeObjects', 'storeArgs' => ['idProperty' => 'idg']]
                ],
                'objToEdit' => ['toNumeric' => ['class' => 'TukosLib\Utils\Utilities', 'id']],
                'editToObj' => ['toAssociative' => ['class' => 'TukosLib\Utils\Utilities', 'id']],
            ];
            $this->_exceptionCols['grid'][] = 'worksheet';
            $this->jsonColsPathsView['worksheet'] = [];
        }

        if (in_array('history', $this->model->allCols)){
            $historyGridCols = array_diff($this->gridCols(), ['created', 'creator', 'history']);
/*
            $this->dataWidgets['history'] = [
                'type' => 'storeDgrid', 
                'atts' => ['edit' => [
                        'label' => $this->tr('history'),
                        'object' => $this->objectName,
                        'colsDescription' => $this->widgetsDescription($historyGridCols, false), 
                        'objectIdCols' => array_values(array_intersect($historyGridCols, $this->model->idCols)),
                        'maxHeight' => '300px', 'colspan' => 1, 'disabled' => true,
                    ]
                ],
            ];
*/
            $this->dataWidgets['history'] = [
                'type' => 'simpleDgrid',
                'atts' => ['edit' =>[
                    'label'           => $this->tr('history'),
                    'colsDescription' => $this->widgetsDescription($historyGridCols, false),
                    'objectIdCols'    => array_values(array_intersect($historyGridCols, $this->model->idCols)),
                    'gridMode' => 'overview',
                    'maxHeight' => '300px', 'colspan' => 1, 'disabled' => true,
                ]],
            ];
            
            $this->_exceptionCols['grid'][] = 'history';
            $this->_exceptionCols['post'][] = 'history';
        }
    }
       
    public function dataElts($viewMode='edit'){
        return array_values(array_diff(array_keys($this->dataWidgets), $this->_exceptionCols[$viewMode]));
    }
    
    public function widgetsNameTranslations($widgetsName, $editOnly = true){
        foreach ($widgetsName as $widgetName){
            if (isset($this->dataWidgets[$widgetName])){
                $translations[$widgetName] = mb_strtolower(Widgets::description($this->dataWidgets[$widgetName], $editOnly)['atts']['label']);
            }else{
                Feedback::add('Notavalidwidgetname' . ': ' . $widgetName);
            }
        }
        return $translations;
    }

    public function widgetsNameUntranslations($translatedWidgetsName, $editOnly = true){
        $untranslations = []; $translations = []; 
        $dataWidgets = $this->dataWidgets;
        foreach ($dataWidgets as $name => $description){
            $untranslations[mb_strtolower(Widgets::description($description, $editOnly)['atts']['label'])] = $name;            
        }
        foreach ($translatedWidgetsName as $translatedWidgetName){
            $lcTranslatedWidgetName = mb_strtolower($translatedWidgetName);
            if (isset($untranslations[$lcTranslatedWidgetName])){
                $translations[$untranslations[$lcTranslatedWidgetName]] = $translatedWidgetName;
            }else{
                Feedback::add('Couldnotfinduntranslationfor' . ': ' . $translatedWidgetName);
                $translations[$lcTranslatedWidgetName] = $translatedWidgetName;
            }
        }
        return $translations;
    }

    function allowedGetCols(){
        return array_values(array_diff($this->model->allCols, $this->_exceptionCols['get']));
    }
    
    function sendOnSave(){
    	return $this->sendOnSave;
    }
    
    function sendOnDelete(){
    	return $this->sendOnDelete;
    }
    
    function doNotEmpty(){
    	return (empty($this->doNotEmpty) ? null : $this->doNotEmpty);
    }
    
    function gridCols(){// cols which are rendered on the overview grid & on subobjects grids

        return array_values(array_diff(
            array_merge(['id', 'parentid', 'name'], $this->addedDataWidgetsElts, ['comments', 'permission', 'grade',  'contextid', 'updator', 'updated', 'creator', 'created'], (in_array('custom', $this->model->allCols) ? ['custom'] : []),
                isset($this->dataWidgets['configstatus']) ? ['configstatus'] : []), 
            $this->_exceptionCols['grid']
        ));
    }


    function widgetsDescription($elements, $editOnly = true){
        $result = [];
        foreach ($elements as $id){
            $result[$id] = Widgets::description($this->dataWidgets[$id], $editOnly);
        }
        return $result;
    }
    function tabEditTitle ($values){
        if (empty($values['id'])){
        	return $this->tr($this->objectName) . ' (' . $this->tr('new') . ')';
        }else{
        	$name = SUtl::translatedExtendedName($this->model, $values['id']);
        }
        return $name . ' (' . ucfirst($this->tr($this->objectName)) . '  '  . $values['id'] . ')';
    }
}
?>
