<?php
namespace TukosLib\Objects\Sports;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\ViewUtils;

class GoldenCheetah {
    public const numberFormat = ['renderCell' => 'renderContent', 'formatType' => 'number', 'formatOptions' => ['places' => 0], 'gcToTukos' => 'number'];
    public const durationFormat = ['renderCell' => 'renderContent', 'formatType' => 'tHHMMSSToHHMMSS', 'gcToTukos' => 'secondsToTime'];
    public const durationMMFormat = ['renderCell' => 'renderContent', 'formatType' => 'tHHMMSSToHHMM', 'gcToTukos' => 'secondsToTime'];
    private static $widgets = null; private static $permanentGc = null; private static $nonGc = null; private static $metricsColsDescription = null; private static $sessionsWidgetsDescription = null;
    private static $sessionsColsDefinition = null; private static $permanentGcOptions = null; private static $customMetrics = null;

    public static function init(){
        self::$widgets = [
            'duration' => ['gcName' => 'Duration', 'description' => ['gcCols' => self::durationMMFormat]],
            'distance' => ['gcName' => 'Distance', 'description' => ['gcCols' => Utl::array_merge_recursive_replace(self::numberFormat, ['formatOptions' => ['places' => 1], 'headerTail' => ' (km)'])]],
            'elevationgain' => ['gcName' => 'Elevation_Gain', 'description' => ['gcCols' => array_merge(self::numberFormat, ['headerTail' => ' (m)'])]],
            'timemoving' => ['gcName' => 'Time_Riding', 'description' => ['sessions' => ['colType' => 'VARCHAR(30)  DEFAULT NULL', 'widgetType' => 'minutesTextBox'], 'gcCols' => self::durationFormat]],
            'gctriscore' => ['gcName' => 'TriScore', 'description' => ['sessions' => ['colType' => 'VARCHAR(10) DEFAULT NULL', 'widgetType' => 'numberTextBox'], 'gcCols' => self::numberFormat]],
            'gcavghr' => ['gcName' => 'Average_Heart_Rate', 'description' => ['sessions' => ['colType' => 'MEDIUMINT DEFAULT NULL', 'widgetType' => 'numberTextBox'], 'gcCols' => self::numberFormat]],
            'gc95hr' => ['gcName' => '95%_Heartrate', 'description' => ['sessions' => ['colType' => 'MEDIUMINT DEFAULT NULL', 'widgetType' => 'numberTextBox'], 'gcCols' => self::numberFormat]], 
            'gctrimphr' => ['gcName' => 'Tukos_TRIMP_Heart_rate', 'description' => ['sessions' => ['colType' => 'MEDIUMINT DEFAULT NULL', 'widgetType' => 'numberTextBox'], 'gcCols' => self::numberFormat]],
            'gctrimppw' => ['gcName' => 'Tukos_TRIMP_Power', 'description' => ['sessions' => ['colType' => 'MEDIUMINT DEFAULT NULL', 'widgetType' => 'numberTextBox'], 'gcCols' => self::numberFormat]],
            'gcmechload' => ['gcName' => 'Tukos_Mechanical_Load', 'description' => ['sessions' => ['colType' => 'MEDIUMINT DEFAULT NULL', 'widgetType' => 'numberTextBox'], 'gcCols' => self::numberFormat]],
            'gch4time' => ['gcName' => 'H4_Time_in_Zone', 'description' => ['sessions' => ['colType' => 'VARCHAR(10) DEFAULT NULL', 'widgetType' => 'secondsTextBox'], 'gcCols' => self::durationFormat]],
            'gch5time' => ['gcName' => 'H5_Time_in_Zone', 'description' => ['sessions' => ['colType' => 'VARCHAR(10) DEFAULT NULL', 'widgetType' => 'secondsTextBox'], 'gcCols' => self::durationFormat]],
        ];
        self::$permanentGc = [
            'startdate' => ['gcName' => 'date', 'description' => ['gcCols' => ['label' => 'date', 'renderCell' => 'renderContent', 'formatType' => 'date', 'gcToTukos' => '/to-']]],
            'time' => ['gcName' => 'time', 'description' => ['gcCols' => []]],
            'sport' => ['gcName' => '"Sport"', 'description' => ['gcCols' => ['renderCell' => 'renderContent', 'formatType' => 'translate', 'formatOptions' => ['object' => 'sptprograms'],
                'gcToTukos' => 'sliceOneAndGcToTukos', 'gcToTukosOptions' => ['map' => ['Bike' => 'bicycle', 'Swim' => 'swimming', 'Run' => 'running', 'Workout' => 'other']]]]],
            'name' => ['gcName' => '"Workout_Title"', 'description' => ['gcCols' => ['label' => 'theme', 'gcToTukos' => 'sliceOne']]]
        ];
        self::$nonGc = [
            'selector' => ['selector' => 'checkbox', 'width' => 30],
            'tukosid' => [],
            'sessionid' => []
        ];
        self::$customMetrics = ['gctrimphr', 'gctrimppw', 'gcmechload'];
    }
    public static function gcName($col){
        return self::$widgets[$col]['gcName'];
    }
    public static function metricsColsDescription($tr, $translatorObjectName = 'sptprograms'){
        if (!self::$metricsColsDescription){
            foreach(self::$nonGc as $name => $atts){
                self::$metricsColsDescription[$name] = array_merge($atts, ['label' => $tr($name), 'field' => $name]);
            }
            foreach(self::$widgets as $name =>  $atts){
                $gcName = $atts['gcName'];
                self::$metricsColsDescription[$gcName] = array_merge($atts['description']['gcCols'], ['label' => $tr($gcName), 'field' => $gcName, 'sessionsColName' => $name]);
            }
            foreach(self::$permanentGc as $name => $atts){
                $gcName = $atts['gcName'];
                $description = $atts['description'];
                if ($name === 'sport'){
                    $description['gcCols']['formatOptions']['object'] = "{$translatorObjectName}";
                }
                self::$metricsColsDescription[$gcName] = array_merge($description['gcCols'], 
                    ['label' => $tr(Utl::getItem('label', $description['gcCols'], $name)) . Utl::getItem('headerTail', $description, ''), 'field' => $gcName, 'sessionsColName' => $name]);
            }
        }
        return self::$metricsColsDescription;
    }
    public static function metricsOptions($tr, $cols = []){
        foreach(empty($cols) ? self::$widgets : array_intersect_key(self::$widgets, array_flip($cols)) as $name => $description){
            $gcName = $description['gcName'];
            $metricsOptions[$gcName] = ['option' => in_array($name, self::$customMetrics) ? $gcName : $tr($gcName, 'none'), 'tooltip' => $tr($gcName . 'Tooltip', 'none')];
        }
        return $metricsOptions;
    }
    public static function permanentGcOptions(){
        if (!self::$permanentGcOptions){
            foreach (self::$permanentGc as $description){
                $gcName = $description['gcName'];
                self::$permanentGcOptions[$gcName] = $gcName;
            }
        }
        return self::$permanentGcOptions;
    }
    public static function nonGcCols(){
        return array_keys(self::$nonGc);
    }
    public static function sessionsWidgets($view){
        return array_keys(self::sessionsWidgetsDescription($view));
    }
    public static function sessionsColsDefinition(){
        if (!self::$sessionsColsDefinition){
            foreach(self::$widgets as $name => $description){
                if ($sessionsDescription = Utl::getItem('sessions', $description['description'])){
                    self::$sessionsColsDefinition[$name] = $sessionsDescription['colType'];
                }
            }
         }
         return self::$sessionsColsDefinition;
    }
    public static function colsDefinition($cols){
        foreach ($cols as $col){
            if ($sessionsDescription = Utl::getItem('sessions', self::$widgets[$col]['description'])){
                $definitions[$col] = $sessionsDescription['colType'];
            }
        }
        return $definitions;
    }
    public static function sessionsWidgetsDescription($view){
        if (!self::$sessionsWidgetsDescription){
            foreach(self::$widgets as $name => $description){
                if ($sessionsDescription = Utl::getItem('sessions', $description['description'])){
                    $func = $sessionsDescription['widgetType'];
                    self::$sessionsWidgetsDescription[$name] = ViewUtils::$func($view, [$description['gcName'], [['replace', ['_'/*, '(', ')'*/], ' ']]]);
                }
            }
        }
        return self::$sessionsWidgetsDescription;
    }
    public static function widgetsDescription($view, $cols){
        foreach ($cols as $col){
            if ($sessionsDescription = Utl::getItem('sessions', ($description = self::$widgets[$col])['description'])){
                $func = $sessionsDescription['widgetType'];
                $descriptions[$col] = ViewUtils::$func($view, [$description['gcName'], [['replace', ['_'/*, '(', ')'*/], ' ']]]);
            }
        }
        return $descriptions;
    }
    public static function performedAddedCols(){
        return ['gctriscore', 'gcavghr', 'gc95hr', 'gctrimphr', 'gctrimppw', 'gcmechload', 'gch4time', 'gch5time'];
        
    }
}
GoldenCheetah::init();
?>