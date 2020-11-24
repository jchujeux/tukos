<?php
namespace TukosLib\Objects\Sports;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\ViewUtils;

class GoldenCheetah {
    public const numberFormat = ['renderCell' => 'renderContent', 'formatType' => 'number', 'formatOptions' => ['places' => 0], 'gcToTukos' => 'number'];
    public const durationFormat = ['renderCell' => 'renderContent', 'formatType' => 'tHHMMSSToHHMMSS', 'gcToTukos' => 'secondsToTime'];
    private static $widgets = null; private static $permanentGc = null; private static $nonGc = null; private static $metricsOptions = null; private static $metricsColsDescription = null; private static $sessionsWidgetsDescription = null;
    private static $sessionsColsDefinition = null; private static $permanentGcOptions = null;

    public static function init(){
        self::$widgets = [
            'duration' => ['gcName' => 'Duration', 'description' => ['gcCols' => self::durationFormat]],
            'distance' => ['gcName' => 'Distance', 'description' => ['gcCols' => Utl::array_merge_recursive_replace(self::numberFormat, ['formatOptions' => ['places' => 1], 'headerTail' => ' (km)'])]],
            'elevationgain' => ['gcName' => 'Elevation_Gain', 'description' => ['gcCols' => array_merge(self::numberFormat, ['headerTail' => ' (m)'])]],
            'timemoving' => ['gcName' => 'Time_Riding', 'description' => ['sessions' => ['colType' => 'VARCHAR(30)  DEFAULT NULL', 'widgetType' => 'minutesTextBox'], 'gcCols' => self::durationFormat]],
            'gctriscore' => ['gcName' => 'TriScore', 'description' => ['sessions' => ['colType' => 'VARCHAR(10) DEFAULT NULL', 'widgetType' => 'numberTextBox'], 'gcCols' => self::numberFormat]],
            'gcavghr' => ['gcName' => 'Average_Heart_Rate', 'description' => ['sessions' => ['colType' => 'MEDIUMINT DEFAULT NULL', 'widgetType' => 'numberTextBox'], 'gcCols' => self::numberFormat]],
            'gc95hr' => ['gcName' => '95%_Heartrate', 'description' => ['sessions' => ['colType' => 'MEDIUMINT DEFAULT NULL', 'widgetType' => 'numberTextBox'], 'gcCols' => self::numberFormat]], 
            'gctrimp100' => ['gcName' => 'TRIMP_exponential_(100)', 'description' => ['sessions' => ['colType' => 'MEDIUMINT DEFAULT NULL', 'widgetType' => 'numberTextBox'], 'gcCols' => self::numberFormat]],
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
    }
    public static function metricsColsDescription($tr){
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
                self::$metricsColsDescription[$gcName] = array_merge($description['gcCols'], 
                    ['label' => $tr(Utl::getItem('label', $description['gcCols'], $name)) . Utl::getItem('headerTail', $description, ''), 'field' => $gcName, 'sessionsColName' => $name]);
            }
        }
        return self::$metricsColsDescription;
    }
    public static function metricsOptions($tr){
        if (!self::$metricsOptions){
            foreach(self::$widgets as $description){
                $gcName = $description['gcName'];
                self::$metricsOptions[$gcName] = ['option' => $tr($gcName, 'none'), 'tooltip' => $tr($gcName . 'Tooltip', 'none')];
            }
        }
        return self::$metricsOptions;
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
    public static function sessionsWidgetsDescription($view){
        if (!self::$sessionsWidgetsDescription){
            foreach(self::$widgets as $name => $description){
                if ($sessionsDescription = Utl::getItem('sessions', $description['description'])){
                    $func = $sessionsDescription['widgetType'];
                    self::$sessionsWidgetsDescription[$name] = ViewUtils::$func($view, [$description['gcName'], [['replace', ['_', '(', ')'], ' ']]]);
                }
            }
        }
        return self::$sessionsWidgetsDescription;
    }
}
GoldenCheetah::init();
?>