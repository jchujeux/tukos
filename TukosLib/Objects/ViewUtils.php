<?php
/**
 *
 * Static class for supporting AbstractView and its instantiations
 */
namespace TukosLib\Objects;

use TukosLib\Objects\Directory;
use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\TukosFramework as Tfk;

class ViewUtils{

    const utl = 'TukosLib\Utils\Utilities';
    const dutl = 'TukosLib\Utils\DateTimeUtilities';
    
	static public function textBox($view, $label, $custom=[]){
        return Utl::array_merge_recursive_replace(['type' => 'textBox', 'atts' => ['edit' => ['label' => $view->tr($label)]]], $custom);
    }
    static public function textArea($view, $label, $custom=[]){
    	return Utl::array_merge_recursive_replace(['type' => 'textArea', 'atts' => ['edit' => ['label' => $view->tr($label)]]], $custom);
    }

	static public function colorPickerTextBox($view, $label, $custom=[]){
        return Utl::array_merge_recursive_replace(['type' => 'colorPickerTextBox', 'atts' => ['edit' => ['label' => $view->tr($label)]]], $custom);
    }

	static public function dropDownTextBox($view, $label, $custom=[]){// need to provide dropdown widget, e.g. , ['edit' => ['dropDownWidget' => ['type' => 'colorPalette', 'atts' => []]]]
        return Utl::array_merge_recursive_replace(['type' => 'dropDownTextBox', 'atts' => ['edit' => ['label' => $view->tr($label)]]], $custom);
    }
    static public function htmlContent($view, $label, $custom=[]){
        return Utl::array_merge_recursive_replace(['type' => 'htmlContent', 'atts' => ['edit' => ['label' => $view->tr($label)]]], $custom);
    }
    static public function numberTextBox($view, $label, $custom=[]){
        return Utl::array_merge_recursive_replace([
        		'type' => 'numberTextBox', 
            'atts' => ['edit' => ['label' => $view->tr($label), 'constraints' => ['pattern' =>  "0.######"]]],
            //'objToEdit' => ['floatval' => []],  'objToStoreEdit' => ['floatval' => []],  'objToOverview' => ['floatval' => []],
        	],
        	$custom);
    }
    static public function tukosNumberBox($view, $label, $custom=[]){
        return Utl::array_merge_recursive_replace([
        		'type' => 'tukosNumberBox',
        		'atts' => ['edit' => ['label' => $view->tr($label), 'constraints' => ['pattern' => '##.']]],
        		//'objToEdit' => ['floatval' => []],  'objToStoreEdit' => ['floatval' => []],
        		'editToObj' => ['blankToNull' => ['class' => self::utl]], 'storeEditToObj' => ['blankToNull' => ['class' => self::utl]], 'overviewToObj' => ['blankToNull' => ['class' => self::utl]], 
        	],
        	$custom);
    }
    static public function currencyTextBox($view, $label, $custom = []){
        return Utl::array_merge_recursive_replace([
        		'type' => 'currencyTextBox',
            	'atts' => ['edit' => ['title' => $view->tr($label), 'style' => ['width' => '5em'], 'lang' => 'fr-fr', 'currency' => 'EUR']],
        		'objToEdit' => ['floatval' => []],  'objToStoreEdit' => ['floatval' => []],],
            $custom
        );
    }
    static public function tukosCurrencyBox($view, $label, $custom = []){
        return Utl::array_merge_recursive_replace([
        		'type' => 'tukosCurrencyBox',
        		'atts' => ['edit' => ['title' => $view->tr($label), 'style' => ['width' => '5em'], 'lang' => 'fr-fr', 'currency' => 'EUR']],
        		'objToEdit' => ['floatval' => []],  'objToStoreEdit' => ['floatval' => []],],
            $custom
        );
    }
    static public function tukosDateBox($view, $label, $custom=[]){
        return Utl::array_merge_recursive_replace(['type' => 'tukosDateBox', 'atts' => [
        		'edit' => ['label' => $view->tr($label)]],  
        		'editToObj' => ['blankToNull' => ['class' => self::utl]], 'storeEditToObj' => ['blankToNull' => ['class' => self::utl]], 'overviewToObj' => ['blankToNull' => ['class' => self::utl]], 
        		'format' => ['type' => 'date']
        	], $custom);
    }
    static public function checkBox($view, $label, $custom=[]){
        return Utl::array_merge_recursive_replace(['type' => 'checkBox', 'atts' => ['edit' => ['label' => $view->tr($label), 'value' => 'YES']]], $custom);
    }
    static public function editor($view, $label, $custom=[]){
           return Utl::array_merge_recursive_replace([
                    'type' => 'editor',
                    'atts' => ['edit' =>  ['label' => $view->tr($label), 'height' => '100%']],
                    'objToEdit' => ['nullToBlank' => ['class' => 'TukosLib\Utils\Utilities']],
                    'objToStoreEdit' => ['nullToBlank' => ['class' => 'TukosLib\Utils\Utilities']],
                ],
                $custom);
    }
    static public function lazyEditor($view, $label, $custom=[]){
    	return Utl::array_merge_recursive_replace([
    			'type' => 'lazyEditor',
    			'atts' => ['edit' =>  ['label' => $view->tr($label), 'height' => Utl::drillDown($custom, ['atts', 'edit', 'height'], '100px'), 'style' => ['backgroundColor' => 'white', 'color' =>  'initial', 'minHeight' =>  '5em']]],
    			'objToEdit' => ['nullToBlank' => ['class' => 'TukosLib\Utils\Utilities']],
    			'objToStoreEdit' => ['nullToBlank' => ['class' => 'TukosLib\Utils\Utilities']],
    	],
    			$custom);
    }
    
    static public function objectSelect($view, $label, $object, $custom=[]){
        return Utl::array_merge_recursive_replace(['type' => 'ObjectSelect', 'atts' => ['edit' => ['label' => $view->tr($label), 'object' => $object, 'dropdownFilters' => ['contextpathid' => '$tabContextId']]]], $custom);
    }

    static public function restSelect($view, $label, $object, $custom=[]){
        return Utl::array_merge_recursive_replace(['type' => 'RestSelect', 'atts' => ['edit' => ['label' => $view->tr($label), 'object' => $object, 'dropdownFilters' => ['contextpathid' => '$tabContextId']]]], $custom);
    }

    static public function objectSelectMulti($widgetNameOrObjects, $view, $label, $custom=[]){
        $objects = is_string($widgetNameOrObjects) ? $view->model->idColsObjects[$widgetNameOrObjects] : $widgetNameOrObjects;
        if (count($objects) === 1){
            return self::objectSelect($view, $label, $objects[0], $custom); 
        }else{
            $editAtts = ['label' => $view->tr($label), 'defaultObject' => $objects[0]];
            foreach ($objects as $object){
                $editAtts['items'][$object] = ['label' => $view->tr($object), 'object' => $object, 'dropdownFilters' => ['contextpathid' => '$tabContextId']];
            }
            return Utl::array_merge_recursive_replace(['type' => 'objectSelectMulti', 'atts' => ['edit' =>  $editAtts]], $custom);
        }
    }

    static public function storeSelect($optionsName, $view, $label, $storeOptions=[true, 'ucfirst', false, false], $custom=[]){
        return Utl::array_merge_recursive_replace([
            'type' => 'storeSelect',
            'atts' => ['edit' =>  ['storeArgs' => ['data' => Utl::idsNamesStore($view->model->options($optionsName), $view->tr, $storeOptions)], 'label' => $view->tr($label)]/*, 'style' => ['maxWith' => '10em']*/],
        ],
            $custom
            );
    }
    
    static public function storeComboBox($optionsName, $view, $label, $storeOptions=[true, 'ucfirst', false, false], $custom=[]){
        return Utl::array_merge_recursive_replace([
            'type' => 'storeComboBox',
            'atts' => ['edit' =>  ['storeArgs' => ['data' => Utl::idsNamesStore($view->model->options($optionsName), $view->tr, $storeOptions)], 'label' => $view->tr($label)]/*, 'style' => ['maxWith' => '10em']*/],
        ],
            $custom
            );
    }
    
    static public function numberUnitBox($optionsName, $view, $label, $custom=[], $trOptions = null){
        return Utl::array_merge_recursive_replace([
                'type' => 'numberUnitBox', 
                'atts' => ['edit' => ['label' => $view->tr($label), 'unit'  => ['storeArgs' => ['data' => Utl::idsNamesStore($view->model->options($optionsName), $view->tr, $trOptions)]]]]
            ],
            $custom
        );
    }
    static public function timeTextBox($view, $label, $custom=[]){
        return  Utl::array_merge_recursive_replace(['type' => 'timeTextBox' , 'atts' => ['edit' =>  ['label' => $view->tr($label)]]], $custom);
    }
    static public function secondsTextBox($view, $label, $custom=[]){
        return  Utl::array_merge_recursive_replace(['type' => 'timeTextBox' , 'atts' => [
            'edit' =>  ['label' => $view->tr($label), 'constraints' => ['timePattern' => 'HH:mm:ss', 'clickableIncrement' => 'T00:15:00', 'visibleRange' => 'T01:00:00']],
            'storeedit' => ['formatType' => 'tHHMMSSToHHMMSS'],
            'overview' => ['formatType' => 'secondssToHHMMSS']
        ],
            'objToEdit' => ['secondsToTime' => ['class' => self::dutl]],
            'editToObj' => ['timeToSeconds' => ['class' => self::dutl]],
            'objToStoreEdit' => ['secondsToTime' => ['class' => self::dutl]],
            'storeEditToObj' => ['timeToSeconds' => ['class' => self::dutl]],
        ], $custom);
    }
    static public function minutesTextBox($view, $label, $custom=[]){
        return  Utl::array_merge_recursive_replace(['type' => 'timeTextBox' , 'atts' => [
            'edit' =>  ['label' => $view->tr($label), 'constraints' => ['timePattern' => 'HH:mm', 'clickableIncrement' => 'T00:15', 'visibleRange' => 'T01:00']],
            'storeedit' => ['formatType' => 'tHHMMSSToHHMM', 'width' => 70],
            'overview' => ['formatType' => 'minutesToHHMM', 'width' => 70]
        ],
            'objToEdit' => ['minutesToTime' => ['class' => self::dutl]],
            'editToObj' => ['timeToMinutes' => ['class' => self::dutl]],
            'objToStoreEdit' => ['minutesToTime' => ['class' => self::dutl]],
            'storeEditToObj' => ['timeToMinutes' => ['class' => self::dutl]],
        ], $custom);
    }
    static public function dateTimeBoxDataWidget($view, $label, $custom=[]){
        return  Utl::array_merge_recursive_replace([
            'type' => 'dateTimeBox' ,
            'atts' => ['edit' =>  ['label' => $view->tr($label)],
                'storeedit' => ['width' => 85,  'formatType' => 'datetime'],
                'overview' => ['width' => 85,  'formatType' => 'datetime'],
            ],
            'objToEdit'        => ['toUTC' => []], 'editToObj'         => ['fromUTC' => [], 'blankToNull' => ['class' => self::utl]],
            'objToStoreEdit'   => ['toUTC' => []], 'storeEditToObj'    => ['fromUTC' => [], 'blankToNull' => ['class' => self::utl]],
            'objToOverview'    => ['toUTC' => []], 'overviewToObj'     => ['fromUTC' => [], 'blankToNull' => ['class' => self::utl]],
            //	'editToObj' => ['blankToNull' => ['class' => self::utl]], 'storeEditToObj' => ['blankToNull' => ['class' => self::utl]], 'overviewToObj' => ['blankToNull' => ['class' => self::utl]],
        ],
            $custom
            );
    }
    public static function timeStampDataWidget($view, $label, $custom=[]){
         return  Utl::array_merge_recursive_replace([
                'type' => 'formattedTextBox',       
                'atts' => ['edit' => ['label' => $view->tr($label), 'style' => ['width' => '10em'], 'formatType' => 'datetimestamp'],
                      'storeedit' => ['width' => 85,  'formatType' => 'datetime'],
                       'overview' => ['width' => 85,  'formatType' => 'datetime'],
                ],
             'objToEdit'        => ['toUTC' => []], 'editToObj'         => ['fromUTC' => [], 'blankToNull' => ['class' => self::utl]],
             'objToStoreEdit'   => ['toUTC' => []], 'storeEditToObj'    => ['fromUTC' => [], 'blankToNull' => ['class' => self::utl]],
             'objToOverview'    => ['toUTC' => []], 'overviewToObj'     => ['fromUTC' => [], 'blankToNull' => ['class' => self::utl]],
        	//	'editToObj' => ['blankToNull' => ['class' => self::utl]], 'storeEditToObj' => ['blankToNull' => ['class' => self::utl]], 'overviewToObj' => ['blankToNull' => ['class' => self::utl]], 
         ],
            $custom
        );
    }
    public static function ISODateTimeBoxDataWidget($view, $label, $custom=[]){
         return  Utl::array_merge_recursive_replace([
                'type' => 'dateTimeBox' ,  
                'atts' => ['edit' =>  ['label' => $view->tr($label)],
                      'storeedit' => ['width' => 85],
                       'overview' => ['width' => 85],
                ],
        		'editToObj' => ['blankToNull' => ['class' => self::utl]], 'storeEditToObj' => ['blankToNull' => ['class' => self::utl]], 'overviewToObj' => ['blankToNull' => ['class' => self::utl]], 
         ],
            $custom
        );
    }
    public static function widgetsArrayDescription($colsDescription, $editOnly = true){
        $result = [];
        foreach ($colsDescription as $col => $colDescription){
            if (isset($colDescription['type'])){
                $result[$col] = Widgets::description($colDescription, $editOnly);
            }else{
                $result[$col] = $colDescription;
            }
        }
        return $result;
    }
    public static function jsonGrid($view, $label, $colsDescription, $custom=[]){
        return  Utl::array_merge_recursive_replace([
                'type' => 'SimpleDgrid',
                'atts' => ['edit' =>  [
                    'label' => $view->tr($label), 'storeType' => 'MemoryTreeObjects', 'storeArgs' => ['idProperty' => 'idg'], 'initialId' => true,
                    'colsDescription' => self::widgetsArrayDescription($colsDescription, false),
                ]],
                'objToEdit' => ['toNumeric' => ['class' => 'TukosLib\Utils\Utilities', 'id']],
                'editToObj' => ['toAssociative' => ['class' => 'TukosLib\Utils\Utilities', 'id']],
            ],
            $custom
        );
    }
    public static function basicGrid($view, $label, $columns, $custom=[]){
        return  Utl::array_merge_recursive_replace(
            ['type' => 'BasicGrid', 'atts' => ['edit' => ['label' => $view->tr($label), 'storeType' => 'MemoryTreeObjects', 'columns' => $columns]]],
            $custom
            );
    }
    public static function onDemandGrid($view, $label, $columns, $custom=[]){
        return  Utl::array_merge_recursive_replace(
            ['type' => 'OnDemandGrid', 'atts' => ['edit' => ['label' => $view->tr($label), 'columns' => $columns]]],
            $custom
            );
    }
}
?>
