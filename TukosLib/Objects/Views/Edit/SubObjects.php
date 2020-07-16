<?php
namespace TukosLib\Objects\Views\Edit;

use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;

use TukosLib\TukosFramework as Tfk;

class SubObjects {

    public static function addWidgets($subObjects, $view){
        $objectsStore = Tfk::$registry->get('objectsStore');
        foreach ($subObjects as $widgetName => &$subObject){
            if (empty($subObject['object'])){
                $view->subObjects[$widgetName]['object'] = $subObject['object'] = $widgetName;
            }
            if (empty($subObject['view'])){
                $view->subObjects[$widgetName]['view'] = $subObject['view']      = $objectsStore->objectView($subObject['object']);
            }
            if (empty($subObject['model'])){
                $view->subObjects[$widgetName]['model'] = $subObject['model']     = $objectsStore->objectModel($subObject['object'], $subObject['view']->tr);
            }
            if (empty($subObject['widgetCols'])){
                $view->subObjects[$widgetName]['widgetCols'] = $subObject['widgetCols']= $subObject['view']->gridCols();
            }
            if (!empty($subObject['removeCols'])){
                $view->subObjects[$widgetName]['widgetCols'] = $subObject['widgetCols'] = array_diff($view->subObjects[$widgetName]['widgetCols'], $subObject['removeCols']);
            }
            if (empty($subObject['getCols'])){
                $view->subObjects[$widgetName]['getCols'] = $subObject['getCols']   = array_intersect($subObject['widgetCols'], $subObject['view']->allowedGetCols());
            }
            if (empty($subObject['initialRowValue'])){
                $view->subObjects[$widgetName]['initialRowValue'] = $subObject['initialRowValue'] = [];
            }
            $defAtts = ['object' => $subObject['object'],
                        'colsDescription' => $subObject['view']->widgetsDescription($subObject['widgetCols'], false), 
        				'sendOnSave'	=> $subObject['view']->sendOnSave(),
        				'sendOnDelete'	=> $subObject['view']->sendOnDelete(),
            			'objectIdCols' => array_values(array_intersect($subObject['widgetCols'], $subObject['model']->idCols)),
                        'maxHeight' => '700px', 'colspan' => 1,
                        'filters' => (isset($subObject['filters']) ? $subObject['filters'] : []),
                        'storeType' => 'MemoryTreeObjects',
                        'storeArgs' => ['idProperty' => 'idg'],
            			'isSubObject' => true,
            			'editDialogAtts' => ['ignoreColumns' => ['contextid', 'permission', 'grade', 'updated', 'updator', 'created', 'creator', 'custom']]
            ];
            foreach ($subObject['filters'] as $col => $target){
                if (is_string($col) && !in_array($col[0], ['&', '#'])){
                    $defAtts['colsDescription'][$col]['atts']['storeedit']['rowsFilters'] =  'disabled';
                }
            }
            $view->dataWidgets[$widgetName] = [
                'type' => 'storeDgrid', 
                'atts' => ['edit' => Utl::array_merge_recursive_replace($defAtts, $subObject['atts'])],
            ];
        }
    }
}
?>
