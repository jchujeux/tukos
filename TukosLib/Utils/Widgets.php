<?php
namespace TukosLib\Utils;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\GridWidgets;
use TukosLib\TukosFramework as Tfk;

/*
 * Class to interact with dojo & tukos widgets and charts
 */

class Widgets{

    use GridWidgets;
    
    public static function description($args, $editOnly = true){
        $widget = $args['type'];
        if ($editOnly){
            $args['atts']['edit'] = self::complete($args['atts']['edit']);
            return self::$widget($args['atts']['edit']);
        }else{
            $args['atts'] = self::complete($args['atts']);
            return self::$widget($args['atts'], false);
        }
    }
    public static function textBox($atts, $editOnly = true){
        $defAtts = [
            'storeedit' => ['minWidth' => 150, 'maxWidth' => 250, 'editOn' => 'click'],
            'overview' => ['minWidth' => 150, 'maxWidth' => 250],
        ];
        return ['type' =>'TextBox', 'atts' => ($editOnly ? $atts : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function formattedTextBox($atts, $editOnly = true){
        $defAtts = [
            'storeedit' => ['width' => 200, 'editOn' => 'click'],
            'overview' => ['width' => 200],
        ];
        return ['type' =>'FormattedTextBox', 'atts' => ($editOnly ? $atts : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function colorPickerTextBox($atts, $editOnly = true){
    	$defAtts = [
    			'edit' => ['style' => ['width' => '7em']],
    			'storeedit' => ['width' => 80, 'editOn' => 'click', 'renderCell' => 'renderColorPicker'],
    			'overview' => ['width' => 80, 'renderCell' => 'renderColorPicker'],
    	];
    	return ['type' =>'ColorPickerTextBox', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function htmlContent($atts, $editOnly = true){
        $defAtts = [
            'edit' => [],
        	'storeedit' => ['minWidth' => 150],
            'overview' => ['mnWidth' => 150],
        ];
        return ['type' =>'HtmlContent', 'atts' => ($editOnly ? $atts : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function objectEditor($atts, $editOnly = true){
        $defAtts = [
            'storeedit' => ['editorArgs' => ['style' => ['width' => '10em']], 'width' => 200, 'editOn' => 'click'],
            'overview' => ['minWidth' => 150],
        ];
        return ['type' =>'ObjectEditor', 'atts' => ($editOnly ? $atts : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function numberTextBox($atts, $editOnly = true){
        $defAtts = [
            'edit' => ['style' => ['width' => 'auto', 'maxWidth' => '5em']],
            'storeedit' => ['editorArgs' => ['style' => ['width' => '3em']], 'width' => 60, 'editOn' => 'click'],
            'overview' => ['width' => 60],
        ];
        return ['type' =>'NumberTextBox', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function tukosNumberBox($atts, $editOnly = true){
        $defAtts = [
            'edit' => ['style' => ['width' => 'auto', 'maxWidth' => '5em']],
            'storeedit' => ['editorArgs' => ['style' => ['width' => '5em']], 'width' => 60, 'editOn' => 'click'],
            'overview' => ['width' => 60],
        ];
        return ['type' =>'TukosNumberBox', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function currencyTextBox($atts, $editOnly = true){
        $defAtts = [
            'storeedit' => ['editorArgs' => ['style' => ['width' => '4em']], 'width' => 70, 'editOn' => 'click'],
            'overview' => ['width' => 70],
        ];
        return ['type' =>'CurrencyTextBox', 'atts' => ($editOnly ? $atts : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function tukosCurrencyBox($atts, $editOnly = true){
        $defAtts = [
            'storeedit' => ['editorArgs' => ['style' => ['width' => '4em']], 'width' => 70, 'editOn' => 'click'],
            'overview' => ['width' => 70],
        ];
        return ['type' =>'TukosCurrencyBox', 'atts' => ($editOnly ? $atts : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function checkBox($atts, $editOnly = true){
        $defAtts = [
            'storeedit' => ['width' => 50/*, 'editOn' => 'click'*/, 'renderCell' => 'renderCheckBox'],
            'overview' => ['width' => 50/*, 'editOn' => 'click'*/, 'renderCell' => 'renderCheckBox'],
        ];
        return ['type' =>'CheckBox', 'atts' => ($editOnly ? $atts : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function textArea($atts, $editOnly = true){
        $defAtts = ['storeedit' => ['minWidth' => 100, 'editOn' => 'click']];
        return ['type' =>'Textarea', 'atts' => ($editOnly ? $atts : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function tukosTextArea($atts, $editOnly = true){
        $defAtts = ['storeedit' => ['editOn' => 'click', 'minWidth' => 100]];
        return ['type' =>'TukosTextarea', 'atts' => ($editOnly ? $atts : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function tukosDateBox($atts, $editOnly = true){
        $defAtts = [
            'edit' => ['style' => ['width' => '6em']],
            'storeedit' => ['width' => 100, 'editOn' => 'click'],
            'overview'  => ['width' => 100],
        ];
        return ['type' =>'TukosDateBox', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function timeTextBox($atts, $editOnly = true){
        $defAtts = ['edit' => ['constraints' => ['timePattern' => 'HH:mm:ss', 'clickableIncrement' => 'T00:15:00', 'visibleRange' => 'T01:00:00']],
                    'storeedit' => ['minWidth' => 70, 'editOn' => 'click'],
                    'overview'  => ['minWidth' => 70],
        ];
        return ['type' =>'TimeTextBox', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts)) ];
    }
    public static function dateTimeBox($atts, $editOnly = true){
        $defAtts = ['edit' => ['dateArgs'  => ['style' => ['width' => '6em']],
                               'timeArgs'  => ['style' => ['width' => '5em'], 'value' => 'T00:00:00', 
                                           'constraints' => ['timePattern' => 'HH:mm:ss', 'clickableIncrement' => 'T00:15:00', 'visibleRange' => 'T01:00:00']]],
                    'storeedit' => ['width' => 100, 'editOn' => 'click'],
                    'overview'  => ['width' => 100],
                   ];
        return ['type' =>'DateTimeBox', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts)) ];
    }
    public static function editor($atts, $editOnly = true){
        $defAtts = [
            'edit' => [/*'style' => ['fontFamily' => 'courier']*/],
            'storeedit' => ['editOn'  => 'click',],
        ];
        return ['type' =>'Editor', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function lazyEditor($atts, $editOnly = true){
    	$defAtts = [
            'edit' => [],
    		'storeedit' => ['editOn'  => 'click', 'minWidth' => 200],
    	    'overview' => ['minWidth' => 150]
    	];
    	return ['type' =>'LazyEditor', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function lazyWidget($atts, $editOnly = true){
        return array_merge(call_user_func([__NAMESPACE__ .'\Widgets', $editOnly ? $atts['unhideWidget'] : $atts['edit']['unhideWidget']], $atts, $editOnly, true), ['type' => 'LazyWidget']);
    }
    public static function objectSelect($atts, $editOnly = true){
        $defAtts = [
            'edit' => [
                'title' => '', 'placeHolder' => Tfk::tr('Select a name'), 'searchAttr' => 'name', 'searchDelay' => 500, 'required' => false,
            	'style' => ['width' => "auto", 'minWidth' => '5em', 'maxWidth' => '15em'], 'fetchProperties' => ['sort' => [['attribute' => 'name', 'descending' => false]]], 'ignoreCase' => true,
            ],
            'storeedit' => ['width' => 110, 'editOn' => 'click', 'renderCell' => 'renderNamedId'],
            'overview'  => ['width' => 110, 'renderCell' => 'renderNamedId'],
        ];
        return ['type' => 'ObjectSelect', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function restSelect($atts, $editOnly = true){
    	$defAtts = [
    			'edit' => [
    					'title' => '', 'placeHolder' => Tfk::tr('Select a name'), 'searchAttr' => 'name', 'searchDelay' => 500, 'required' => false, /*'labelProperty' => 'name', 'pageSize' => 500,*/
    					'style' => ['width' => "auto", 'minWidth' => '5em', 'maxWidth' => '30em'], 'fetchProperties' => ['sort' => [['attribute' => 'name', 'descending' => false]]], 'ignoreCase' => true,
    			],
    			'storeedit' => ['width' => 110, 'editOn' => 'click', 'renderCell' => 'renderNamedIdExtra'],
    	    'overview'  => ['width' => 110, 'renderCell' => 'renderNamedIdExtra'],
    	];
    	return ['type' => 'RestSelect', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function objectSelectMulti($atts, $editOnly = true){
        $defAtts = [
            'edit'      => ['title' => '', 'placeHolder' => Tfk::tr('Select a name'), 'style' => ['width' => '12em']],
            'storeedit' => ['width' => 110, 'editOn' => 'click', 'renderCell' => 'renderNamedId'],
            'overview'  => ['width' => 110, 'renderCell' => 'renderNamedId'],
        ];
        if ($editOnly){
            return ['type' => 'ObjectSelectMulti', 'atts' => Utl::array_merge_recursive_replace($defAtts['edit'], $atts)];
        }else{
            return ['type' => 'ObjectSelectMulti', 'atts' => Utl::array_merge_recursive_replace($defAtts, $atts)];
        }
    }
    public static function storeSelect($atts, $editOnly = true){
        $defAtts = [
            'edit' => [
                'title' => '', /*'labelProperty' => 'name', 'labelAttr' => 'name', */'placeHolder' => 'Enter Value', 'required' => false, 'style' => ['width' => "auto", 'minWidth' => '5em', 'maxWidth' => '15em'],
                'storeArgs' => ['data' => null]
            ],
            'storeedit' => ['width' => 110, 'editOn' => 'click', 'renderCell' => 'renderStoreValue'],
            'overview'  => ['width' => 110, 'renderCell' => 'renderStoreValue'],
        ];
        
        return ['type' => 'StoreSelect', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
        
    }
    public static function storeComboBox($atts, $editOnly = true){
        $defAtts = [
            'edit' => [
                'title' => '', /*'labelProperty' => 'name', 'labelAttr' => 'name', */'placeHolder' => 'Enter Value', 'required' => false, 'style' => ['width' => "auto", 'minWidth' => '5em', 'maxWidth' => '15em'],
                'storeArgs' => ['data' => null]
            ],
            'storeedit' => ['width' => 110, 'editOn' => 'click'/*, 'renderCell' => 'renderStoreValue'*/],
            'overview'  => ['width' => 110, 'renderCell' => 'renderStoreValue'],
        ];
        
        return ['type' => 'StoreComboBox', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
        
    }
    public static function numberUnitBox($atts, $editOnly = true){
        $defAtts = ['edit' => ['title' => '', 
                               'number' => ['style' => ['width' => '3em']],
                               'unit'  => ['idProperty' => 'id', 'placeHolder' => Tfk::tr('Unit') . ' ...', 
                                           'required'   => true, 'style' => ['width' => "auto"],
                                           'fetchProperties' =>['sort' => [['attribute' => 'name', 'descending' => false]]],
                                           'storeArgs' => ['data' => null]]],
            'storeedit' => ['width' => 100, 'editOn' => 'click', 'renderCell' => 'renderNumberUnitValue'],
            'overview'  => ['renderCell' => 'renderNumberUnitValue'],
            
        ];
        return ['type' => 'NumberUnitBox', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function simpleUploader($atts, $editOnly = true){
        return ['type' =>'SimpleUploader', 'atts' => $atts];
    }
    public static function uploader($atts, $editOnly = true){
    	return ['type' =>'Uploader', 'atts' => $atts];
    }
    public static function downloader($atts, $editOnly = true){
        return ['type' =>'Downloader', 'atts' => $atts];
    }
    public static function multiSelect($atts, $editOnly = true){
        return ['type' => 'MultiSelect', 'atts' => $atts];
    }
    /*
     * Need to provide at least : ['storeArgs']['data' => $storeData, 'root' => $root, 'paths' => $paths]
     */
    public static function storeTree($atts, $editOnly = true){
        $defAtts = ['edit' => ['title' => '', 
                               'parentProperty' => 'parentid', 'autoExpand' => false]];
        return ['type' => 'StoreTree', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function navigationTree($atts, $editOnly = true){
        $defAtts = ['edit' => ['title' => '', 'autoExpand' => false]];
        return ['type' => 'NavigationTree', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];

    }
    /*
     * Need to provide at least : ['edit']['dropDownWidget']
     */
    public static function dropDownTextBox($atts, $editOnly = true){
        $dropDownWidget = ($editOnly ? Utl::extractItem('dropDownWidget', $atts) :Utl::extractItem('dropDownWidget', $atts['edit']));
        $dropDownWidgetType = $dropDownWidget['type'];
        $defAtts = [
            'edit'      => ['dropDownWidget' => Utl::array_merge_recursive_replace(['atts' => ['style' => ['width' => '15em', 'backgroundColor' => '#F8F8F8']]], self::$dropDownWidgetType($dropDownWidget['atts']))],
            'storeedit' => ['width' => 110, 'editOn' => 'click'],
            'overview'  => ['width' => 110],
        ];
        return ['type' => 'DropDownTextBox', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function objectSelectDropDown($atts, $editOnly = true){
        $dropDownWidget = ($editOnly ? Utl::extractItem('dropDownWidget', $atts) :Utl::extractItem('dropDownWidget', $atts['edit']));
        $dropDownWidgetType = $dropDownWidget['type'];
        $defAtts = [
            'edit'      => ['dropDownWidget' => Utl::array_merge_recursive_replace(['atts' => ['style' => ['width' => '15em', 'backgroundColor' => '#F8F8F8']]], self::$dropDownWidgetType($dropDownWidget['atts']))],
            'storeedit' => ['width' => 110, 'editOn' => 'click', 'renderCell' => 'renderNamedId'],
            'overview'  => ['width' => 110, 'renderCell' => 'renderNamedId'],
        ];
        return ['type' => 'ObjectSelectDropDown', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function colorPalette($atts, $editOnly = true){// supposed to be used in edit mode only
    	$defAtts = [
    			'edit' => [],
    			'storeedit' => ['editOn' => 'click'],
    			'overview' => [],
    	];
    	return ['type' =>'tukos/widgets/ColorPalette', 'atts' => ($editOnly ? $atts : Utl::array_merge_recursive_replace($defAtts, $atts))];
    	 
    }
    public static function pieChart($atts, $editOnly = true){
        $defAtts = ['edit' => [
            'chartStyle' => ['width' => "400px"],
            'title' => '', 'showValuesTable' => 'true',
            'plots' => ['thePlot' => ['type' => 'Pie', 'radius' => 130, 'fontColor' => 'black', 'labelOffset' => 0]],
            'series' => ['thePlot' => ['value' => [], 'options' => ['plot' => 'thePlot']]]
        ]];
        return ['type' => 'Chart', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    
    public static function spiderChart($atts, $editOnly = true){
        $defAtts = ['edit' => [
            'chartStyle' => ['width' => "400px"],
            'title' => '', 'showValuesTable' => 'true',
            'plots' => ['thePlot' => ['type' => 'Spider']],
            'series' => ['thePlot' => ['value' => [], 'options' => ['plot' => 'thePlot']]]
        ]];
        return ['type' => 'Chart', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    
    public static function columnsChart($atts, $editOnly = true){
        $defAtts = ['edit' => [
        	'title' => '', 'showValuesTable' => 'true',
        	'plots' => ['thePlot' => ['type' => 'Columns', 'hAxis' => "x", 'vAxis' =>  "y", 'labels' => true, 'labelStyle' => 'outside', 'gap' => 5, 'minBarSize' => 3, 'maxBarSize' =>  40]],
        	'series' => ['thePlot' => ['value' => [], 'options' => ['plot' => 'thePlot']]]
        ]];
        return ['type' => 'Chart', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function chart($atts, $editOnly = true){
        $defAtts = ['edit' => ['title' => '', 'idProperty' => 'id']];
        return ['type' => 'Chart', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function dynamicChart($atts, $editOnly = true){
        $defAtts = ['edit' => ['title' => '', 'idProperty' => 'id']];
        return ['type' => 'DynamicChart', 'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function horizontalSlider($atts, $editOnly = true){
        $defAtts = ['edit' => []];
        return ['type' => 'HorizontalSlider', 'atts' =>($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function horizontalLinearGauge($atts, $editOnly = true){
        $defAtts = ['edit' => [],
            'storeedit' => ['renderCell' => 'renderGauge', 'editOn' => 'click'],
            'overview'  => ['renderCell' => 'renderGauge'],
        ];
        return ['type' => 'HorizontalLinearGauge', 'atts' =>($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function abcTriangle($atts, $editOnly = true){
        $defAtts = ['edit' => [],
            //'storeedit' => ['renderCell' => 'renderGauge', 'editOn' => 'click'],
            //'overview'  => ['renderCell' => 'renderGauge'],
        ];
        return ['type' => 'ABCTriangle', 'atts' =>($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function StoreCalendar($atts, $editOnly = true){
        $defAtts = ['edit' => ['dateInterval' => 'day',  'createOnGridClick' => true, 'style' => ['position' => 'relative',  'width' => '1000px', 'height' => '1000px',  'storeArgs' => ['data' => null]]]];
        return ['type' => 'StoreCalendar',  'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    public static function StoreSimpleCalendar($atts, $editOnly = true){
        $defAtts = ['edit' => ['dateInterval' => 'day',  'createOnGridClick' => true, 'style' => ['position' => 'relative',  'width' => '1000px', 'height' => '1000px',  'storeArgs' => ['data' => null]]]];
        return ['type' => 'StoreSimpleCalendar',  'atts' => ($editOnly ? Utl::array_merge_recursive_replace($defAtts['edit'], $atts) : Utl::array_merge_recursive_replace($defAtts, $atts))];
    }
    
    /* Widgets with 'edit' array sub-level omitted as used only in edit mode, and called directly, not via self::description
     *
     * Need to provide at least : ['storeArgs']['data' => $storeData, 'root' => $root, 'paths' => $paths], 'urlArgs' => $urlArgs]
     */
    public static function contextTree($atts){
        $defAtts = ['title' => '', 
                    'parentProperty' => 'parentid', 'autoExpand' => false];
        return ['type' => 'ContextTree', 'atts' => array_merge($defAtts, $atts)];
    }
    /*
     * Need to provide at least : $atts['storeArgs'], ['placeHolder']
     * Intended to support different 'selectWidget's, currently only 'ObjectSelect' enabled (as ObjectEdit derives from filteringSelect, as objectSelect does)
     */
    public static function ObjectEdit ($atts){
        $defAtts = ['selectWidget' => 'ObjectSelect', 'style' => ['width' => '120px']];
        $atts = Utl::array_merge_recursive_replace($defAtts, $atts);
        $selectWidget = $atts['selectWidget'];
        unset($atts['selectWidget']);
        return ['type' => 'ObjectEdit', 'atts' => self::$selectWidget($atts)['atts']];
    }
    public static function OverviewEdit ($atts){
        $defAtts = ['selectWidget' => 'ObjectSelect', 'style' => ['width' => '120px']];
        $atts = Utl::array_merge_recursive_replace($defAtts, $atts);
        $selectWidget = $atts['selectWidget'];
        unset($atts['selectWidget']);
        return ['type' => 'OverviewEdit', 'atts' => self::$selectWidget($atts)['atts']];
    }
    public static function tree($atts){
        return ['type' => 'tree', 'atts' => $atts];
    }
}
?>
