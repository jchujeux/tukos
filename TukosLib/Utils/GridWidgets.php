<?php
namespace TukosLib\Utils;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

/*
 * Class to interact with dojo & tukos widgets and charts
 */

trait  GridWidgets{

    public static function complete($attsTarget, $attsSource = null){
        if ($attsSource === null){
            $attsSource = $attsTarget;
        }
        if (empty($attsTarget['label'])){
            $attsTarget['label'] = empty($attsSource['label']) ? (empty($attsSource['title']) ? '' : $attsSource['title']) : $attsSource['label'];
        }
        if (empty($attsTarget['title'])){
            $attsTarget['title'] = empty($attsSource['title']) ? (empty($attsSource['label']) ? $attsTarget['label'] : $attsSource['label']) : $attsSource['title'];
        }
        if (!empty($attsTarget['title']) && empty($attsTarget['placeHolder'])){
            $attsTarget['placeHolder'] = $attsTarget['title'] . ' ... ';
        }
        return $attsTarget;
    }
    public static function basicGrid($atts, $editOnly = true){
        if (isset($atts['columns'])){
            foreach ($atts['columns'] as $key => &$description){
                if (isset($description['formatType']) && ! isset($description['formatter'])){
                    $description['formatter'] = 'formatContent';
                }
            }
        }
        return ['type' =>'BasicGrid', 'atts' => $atts];
    }
    public static function simpleDgrid($atts, $editOnly = true, $lazyEditor = null){
        if ($editOnly){
            $defAtts = ['title' => '', 'colspan' => 1, 'columns' => [], 'initialId'  => true, 'adjustLastColumn' => false];
            $gridMode = Utl::extractItem('gridMode', $atts);
            if (isset($atts['colsDescription'])){
                foreach ($atts['colsDescription'] as $col => $element){
                    if (isset($element['type'])){
                        $defAtts['columns'][$col] = self::colGridAtts($element, $col, !empty($gridMode) ? $gridMode : 'storeedit', $lazyEditor);
                    }else{
                        $defAtts['columns'][$col] = $element;
                    }
                }
                unset($atts['colsDescription']);
            }
            return ['type' => 'SimpleDgrid', 'atts'=>  Utl::array_merge_recursive_replace($defAtts, $atts)];
        }else{
            //Tfk::debug_mode('log', 'SimpleDgrid intended for use only in edit mode');
            //return '';
            $defAtts = [
                'storeedit' => ['editOn' => 'click'],
                'overview' => [],
            ];
            return ['type' => 'TextArea', 'atts' => Utl::array_merge_recursive_replace($defAtts, $atts)];
        }
    }
    public static function accordionGrid($atts, $editOnly = true){
        return array_merge(self::simpleDgrid($atts, $editOnly, true), ['type' => 'AccordionGrid']);
    }
    public static function sheetGrid($atts, $editOnly = true){
         $colDescription = self::description(['type' => 'tukosTextArea', 'atts' => ['storeedit' => ['width' => 50, 'editOn' => 'click, keypress', 'editorArgs' => ['rows' => 1, 'style' => ['height' => '15px']]]]], false);
        if ($editOnly){
            $defAtts = [
               'objectIdCols' => [], 
                'columns' => ['rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'cannotDelete' => true]],
            'sort'  => [['property' => 'rowId', 'descending' => false]], 'itemCustomization' => 'itemCustomization',
                'columnsEdit' => true, 'newColumnArgs' => array_merge(self::colGridAtts($colDescription, null, 'storeedit'), ['sortable' => false]), 'defaultColsNumber' => 10
            ];
            $fieldOrd = ord('A'); 
            $colsNumber = isset($atts['defaultColsNumber']) ? $atts['defaultColsNumber'] : $defAtts['defaultColsNumber'];
            for ($i = 0; $i < $colsNumber; $i++){
            	$field = chr($fieldOrd + $i);
            	$defAtts['columns'][$field] = array_merge($defAtts['newColumnArgs'], ['field' => $field, 'label' => $field]);
            }
            return self::simpleDgrid(Utl::array_merge_recursive_replace($defAtts, $atts), $editOnly);
        }else{
            Tfk::debug_mode('log', 'SheetGrid intended for use only in edit mode');
            return '';
        }
    }
    public static function storeDgrid($atts, $editOnly = true){
        if ($editOnly){
            $defAtts = ['title' => '', 'colspan' => 1, 'columns' => [], 'initialId'  => false, 'keepScrollPosition' => true, 'adjustLastColumn' => false, 'dndParams' => ['skipForm' => true]];
            if (!empty($atts['columns']['selected'])){
            	$defAtts['columns']['selected'] = self::colGridAtts(Utl::extractItem('selected', $atts['columns']), 'selected', 'storeedit');
            }
            foreach ($atts['colsDescription'] as $col => $element){
                $defAtts['columns'][$col] = isset($element['type']) ? self::colGridAtts($element, $col, 'storeedit') : $element;
            }
            unset($atts['colsDescription']);
            return ['type' => 'StoreDgrid', 'atts'=>  Utl::array_merge_recursive_replace($defAtts, $atts)];
        }else{
            Tfk::debug_mode('log', 'StoreDgrid intended for use only in edit mode');
            return '';
        }
    }
    public static function overviewDgrid($atts, $editOnly = true){
        $defAtts = ['title' => '', 
                    'storeArgs' => ['useRangeHeaders' => true], 'allowSelectAll' => true, 'minRowsPerPage' => 25, 'maxRowsPerPage' => 50
        ];
        $defAtts['columns'] = [['selector' => 'checkbox', 'label' => Tfk::tr('selector'), 'width' => 30, 'rowsFilters' =>  false/*, 'unhidable' => true*/]];
        foreach ($atts['colsDescription'] as $col => $description){
            $defAtts['columns'][$col] = self::colGridAtts($description, $col, 'overview');
        }
        unset($atts['colsDescription']);
        return ['type' => 'OverviewDgrid', 'atts'=>  Utl::array_merge_recursive_replace($defAtts, $atts)];
    }

    public static function ganttColumn($atts){
        $date = new \DateTime(null);
        $startDate = $date->format("Y-m-d") .'T00:00:00';
        $date->add(new \DateInterval('P1M'));
        $endDate = $date->format("Y-m-d") .'T00:00:00';
        $defAtts = ['storeedit' => ['scale' => 6000000, 'start' => $startDate, 'end' => $endDate, 'sortable' =>  'false', 
                                    'rowsFilters' => false, 'width' => 500]];
        return ['type' => 'ganttColumn',
                  'atts' => Utl::array_merge_recursive_replace($defAtts, $atts),
                 ];
    }
    
    public static function colGridAtts($element, $col, $mode='storeedit', $lazyEditor = null){
        $defColAtts = [
            'overflow' => 'auto', 'renderHeaderCell' => 'renderHeaderContent',
            'renderCell' => 'renderContent',
            'rowsFilters' => true,
        ];
        $atts = (empty($element['atts'][$mode]) ? [] : $element['atts'][$mode]);
        if (isset($col)){
            $atts['field'] = $col;
        }
        $atts = self::complete($atts, (isset($element['atts']['edit']) ? $element['atts']['edit'] : $atts));
        if ($mode === 'overview'){
            switch ($element['type']){
                case 'StoreSelect':
                    $editorArgs = ['storeArgs' => ['data' => $element['atts']['edit']['storeArgs']['data']]];
                    $atts['editorArgs'] = empty($atts['editorArgs']) ? $editorArgs : Utl::array_merge_recursive_replace($editorArgs, $atts['editorArgs']);
                    break;
                case 'NumberUnitBox':
                    $editorArgs = ['unit' => ['storeArgs' => ['data' => $element['atts']['edit']['unit']['storeArgs']['data']]]];
                    $atts['editorArgs'] = empty($atts['editorArgs']) ? $editorArgs : Utl::array_merge_recursive_replace($editorArgs, $atts['editorArgs']);
            }
        }else{
            $atts['disabled'] = (isset($atts['disabled']) ? $atts['disabled'] : (isset($element['atts']['edit']['disabled']) ? $element['atts']['edit']['disabled'] : false));
            if (!$atts['disabled']){
                if (isset($atts['type'])){
                    $type = Utl::extractItem('type', $atts);
                    $atts['editorArgs'] = self::$type($atts['editorArgs'])['atts'];
                }else{
                    $type = $element['type'];
                    $atts['editorArgs'] = (isset($atts['editorArgs']) 
                        ? (empty($element['atts']['edit']) ? $atts['editorArgs'] : Utl::array_merge_recursive_replace($element['atts']['edit'], $atts['editorArgs']))
                        : (empty($element['atts']['edit']) ? [] : $element['atts']['edit'])
                    );
                }
                if (isset($atts['placeHolder'])){
                		$atts['editorArgs']['placeHolder'] = Utl::extractItem('placeHolder', $atts);
                }
                $atts['editor'] = $type;
                $atts['widgetType'] = $atts['editor'];
                $atts['canEdit'] = (!empty($element['atts'][$mode]['canEdit']) ? $element['atts'][$mode]['canEdit'] : 'canEditRow');
            }
        }
        $atts = Utl::array_merge_recursive_replace($defColAtts, $atts);
        return $atts;
    }
}
?>
