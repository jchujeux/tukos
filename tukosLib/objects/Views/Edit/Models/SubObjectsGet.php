<?php
namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Objects\Views\Edit\Models\SubObjectGet;
use TukosLib\Objects\Views\Edit\Models\SubObjects;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;

use TukosLib\TukosFramework as Tfk;

class SubObjectsGet extends SubObjects{
    use SubObjectGet;

    public static function getData(&$response, $editModelGet, $contextPathId){
        foreach ($editModelGet->view->subObjects as $widgetName => $subObject){
            if (empty($subObject['noServerGet'])){
                $getClass = $editModelGet->objectsStore->objectViewModel($editModelGet->controller, 'Edit', 'Get', ['view' => $subObject['view'], 'model' => $subObject['model']]);
                $response['data']['initialRowValue'][$widgetName] = Utl::array_merge_recursive_replace($getClass->initialize('objToStoreEdit'), $subObject['initialRowValue']);
                $subObject['filters'] = array_merge($subObject['filters'], $editModelGet->view->user->getCustomView($editModelGet->model->objectName, 'edit', ['data', 'filters', $widgetName]));
                $subObjectValue = $getClass->getGrid(
                    ['where' => self::setQuery($subObject['filters'], $response['data']['value'], $contextPathId)], 
                    self::colsToSend($editModelGet, $subObject, $widgetName),
                    (isset($subObject['allDescendants']) ? $subObject['allDescendants'] : false), 
                    'objToStoreEdit'
                )['items'];
                $response['data']['value'][$widgetName] = $subObjectValue;
            }
        }
    }
}
?>
