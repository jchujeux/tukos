<?php
namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Objects\Views\Edit\Models\SubObjectGet;
use TukosLib\Objects\Views\Edit\Models\SubObjects;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;

use TukosLib\TukosFramework as Tfk;

class SubObjectsGet extends SubObjects{
    use SubObjectGet;
    public static function getData(&$response, $editModelGet, $contextPathId, $cols = null){
        $subObjects = empty($cols) ? $editModelGet->view->subObjects : Utl::getItems($cols, $editModelGet->view->subObjects);
        foreach ($subObjects as $widgetName => $subObject){
            if (empty($subObject['noServerGet'])){
                $getClass = $editModelGet->objectsStore->objectViewModel($editModelGet->controller, 'Edit', 'Get', ['view' => $subObject['view'], 'model' => $subObject['model']]);
                $response['data']['initialRowValue'][$widgetName] = Utl::array_merge_recursive_replace($getClass->initialize('objToStoreEdit'), $subObject['initialRowValue']);
                $subObject['filters'] = array_merge($subObject['filters'], $editModelGet->user->getCustomView($editModelGet->model->objectName, 'edit', $editModelGet->paneMode, ['widgetsDescription', $widgetName, 'atts', 'columns', 'filter']));
                $subObjectValue = $getClass->getGrid(
                    self::setQuery($subObject['filters'], $response['data']['value'], $contextPathId), 
                    $subObject['getCols'],//self::colsToSend($editModelGet, $subObject, $widgetName),
                    (isset($subObject['allDescendants']) ? $subObject['allDescendants'] : false), 
                    'objToStoreEdit'
                )['items'];
                $response['data']['value'][$widgetName] = empty($subObjectValue) ? '' : $subObjectValue;// if we return [] on creating a new parent item, _TukosPaneMixin does not call _GridEditMixin.set('value', ...), as get('value') retuns [], and the subObject watch action for value does not get executed
            }
        }
    }
}
?>
