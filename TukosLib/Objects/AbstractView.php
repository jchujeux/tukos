<?php
/**
 *
 * class for viewing methods and properties for the tukos model object
 */
namespace TukosLib\Objects;

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
        $objectsStore = $this->objectsStore = Tfk::$registry->get('objectsStore');
        $this->model = $objectsStore->objectModel($this->objectName, $this->tr);
        $this->user  = Tfk::$registry->get('user');
        $this->sendOnSave = $this->sendOnDelete = ['updated', 'grade'];
        $this->mustGetCols = ['id', 'name', 'parentid', 'updated', 'permission', 'updator', 'creator'];
        $missingUser = $this->tr('missinguser', 'escapeSQuote');
        $needsUser = $this->tr('aclneedsuser', 'escapeSQuote');

        $this->dataWidgets = [
            'id' => ViewUtils::textBox($this, 'Id', [
                    'atts' => [
                        'edit' =>  ['readonly' => true, 'style' => ['width' => '6em', 'backgroundColor' => 'WhiteSmoke']],
                        'storeedit' => ['width' => 80, 'minWidth' => '', 'maxWidth' => '','renderExpando' => true, 'formatter' => 'formatId', 'renderCell' => '', 'editOn' => 'dblClick'],
                        'overview'  => ['width' => 60, 'minWidth' => '', 'maxWidth' => ''],
                    ]
                ]
            ), 
            'parentid'  => ViewUtils::objectSelectMulti($parentObjects, $this, $parentWidgetTitle),
            'name'      => ViewUtils::textBox($this, $nameWidgetTitle, ['atts' => [/*'edit' => ['tukosTooltip' => ['label' => 'Hello world!', 'onClickLink' => ['label' => 'more...', 'name' => 'dailyavg']]], */'storeedit' => ['onClickFilter' => ['id']], 'overview'  => ['onClickFilter' => ['id']]]]),
            'comments'  => ViewUtils::lazyEditor($this, 'CommentsDetails', ['atts' => ['edit' => ['height' => '400px']]]),
            'permission' => ViewUtils::storeSelect('permission', $this, 'Access Control', [true, 'ucfirst', false, false, false], ['atts' => 
                ['edit' => ['readonly' => true, /*'onWatchLocalAction' => ['value' => ['acl' => ['hidden' => ['triggers' => ['server' => true, 'user' => true], 'action' => "return newValue === 'ACL' ? false : true"]]]]*/],
                 'storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]
            ]),
            'acl' => ViewUtils::JsonGrid($this, 'Acl', [
                    'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                    'userid' => ViewUtils::objectSelect($this, 'User', 'users'),
                'permission'  => ViewUtils::storeSelect('acl', $this, 'Permission', [true, 'ucfirst', false, true, false], ['atts' => ['edit' => ['onChangeLocalAction' => ['acl' => ['localActionStatus' => "if (!sWidget.valueOf('userid')){Pmg.alert({title: '$missingUser', content: '$needsUser'})}; return true;"]]]]])
                ],
                ['type' => 'simpleDgrid', 'atts' => ['storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]]
            ),
            'grade' => ViewUtils::storeSelect('grade', $this, 'Grade', null, ['atts' => ['storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]]),
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
            'updator'   => ViewUtils::objectSelect($this, 'Last edited by', 'users', ['atts' => ['edit' => ['placeHolder' => '', 'style' => ['width' => '10em', 'backgroundColor' => 'WhiteSmoke'], 'readonly' => true], 'storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]]),
            'updated'   => ViewUtils::timeStampDataWidget($this, 'Last edit date', ['atts' => ['edit' => ['style' => ['backgroundColor' => 'WhiteSmoke'], 'readonly' => true]]]),
            'creator'   => ViewUtils::objectSelect($this, 'Created by', 'users', ['atts' => ['edit' => ['placeHolder' => '', 'style' => ['width' => '10em', 'backgroundColor' => 'WhiteSmoke'], 'readonly' => true], 'storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]]),
            'created'   => ViewUtils::timeStampDataWidget($this, 'Creation date', ['atts' => ['edit' => ['style' => ['backgroundColor' => 'WhiteSmoke'], 'readonly' => true], 'storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]]),
        ];
        if (Tfk::$registry->isMobile){
            foreach(['permission', 'acl', 'grade', 'contextid', 'updator', 'creator', 'created'] as $widget){
                $this->dataWidgets[$widget]['atts']['edit']['hidden'] = true;
            }
        }
        if ($this->user->rights() === 'SUPERADMIN'){
            $this->dataWidgets['configstatus'] = ViewUtils::storeSelect('configStatus', $this, 'Config Status', null, ['atts' => ['storeedit' => ['hidden' => true], 'overview' => ['hidden' => true]]]);
        }
        $this->defaultDataWidgetsElts = array_keys($this->dataWidgets);
        $this->responseContent = null;
        $this->subObjects = [];
        $this->customFormContent = [];
        $this->toTranslate = [];
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
        $this->jsonColsPathsView     = array_merge(['acl' => []], $jsonColsPathsView);

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
    function allowedGetCols($ignoreExceptions = []){
        return array_values(array_diff($this->model->allCols, array_diff($this->_exceptionCols['get'], $ignoreExceptions)));
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
            array_merge(['id', 'parentid', 'name'], $this->addedDataWidgetsElts, ['comments', 'permission', 'grade',  'contextid', 'updator', 'updated', 'creator', 'created', 'acl'], (in_array('custom', $this->model->allCols) ? ['custom'] : []),
                isset($this->dataWidgets['configstatus']) ? ['configstatus'] : []), 
            $this->_exceptionCols['grid']
        ));
    }
    function dataWidgets(){
        return $this->dataWidgets;
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
            $name = SUtl::translatedExtendedNames([$values['id']])[$values['id']];
        }
        return $name . ' (' . ucfirst($this->tr($this->objectName)) . '  '  . $values['id'] . ')';
    }
    function addToTranslate($toTranslate){
        $this->toTranslate = array_merge($this->toTranslate, $toTranslate);
    }
    function getToTranslate(){
        return property_exists($this, 'toTranslate') ? $this->toTranslate : [];
    }
}
?>
