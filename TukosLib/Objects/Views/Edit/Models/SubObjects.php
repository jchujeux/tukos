<?php
namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Objects\Views\Models\Delete as DeleteModel;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;

use TukosLib\TukosFramework as Tfk;

class SubObjects {

    public static function extractValues($subObjects, &$values){
        $subObjectsValues = [];
        foreach($subObjects as $widgetName => $subObject){
            if (isset($values[$widgetName])){
                $subObjectsValues[$widgetName] = $values[$widgetName];
                unset($values[$widgetName]);
            }
        }
        return $subObjectsValues;
    }
}
?>
