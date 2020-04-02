<?php
/**
 *
 * Provide methods to deal with parent - child methods for object items
 */
namespace TukosLib\Objects;

use TukosLib\Utils\Utilities as Utl;

use TukosLib\TukosFramework as Tfk;

trait ItemJsonCols {

    protected function jsonDecode(&$values, $jsonCols){
        $colsToProcess = array_intersect(array_keys($values), $jsonCols);
        foreach ($colsToProcess as $col){
            $values[$col] = json_decode($values[$col], true);
        }
    }

    protected function jsonEncode(&$values, $jsonFilter){
        $colsToProcess = array_intersect(array_keys($values), $this->jsonCols);
        foreach ($colsToProcess as $col){
            if (is_array($values[$col])){
                if ($jsonFilter || Utl::getItem($col, $this->jsonColsFilters)){
                    $values[$col] = Utl::array_filter_recursive($values[$col],  function($value){
                            return (!empty($value)) || $value === 0;
                    });
                }
                $values[$col] = json_encode($values[$col]);
            }
        }
    }

    protected function drilldown(&$values, $jsonColsKeys, $jsonNotFoundValue){
        $colsToProcess = array_intersect(array_keys($values), array_keys($jsonColsKeys));
        foreach ($colsToProcess as $col){
            $values[$col] = Utl::drillDown((is_string($values[$col]) ? json_decode($values[$col], true) : $values[$col]), $jsonColsKeys[$col], $jsonNotFoundValue);
        }
    }

    protected function jsonNewValues(&$oldValues, &$newValues){
        $colsToProcess = array_intersect(array_keys($newValues), $this->jsonCols);
        foreach ($colsToProcess as $col){
            if (!is_string($newValues[$col])){
                if (Utl::array_contains($oldValues[$col], $newValues[$col])){
                    unset($oldValues[$col]);
                    unset($newValues[$col]);
                }else{
                    $newValues[$col] = Utl::array_merge_recursive_replace($oldValues[$col], $newValues[$col]);
                }
            }
        }
    }
}
?>
