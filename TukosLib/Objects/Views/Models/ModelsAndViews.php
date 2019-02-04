<?php
namespace TukosLib\Objects\Views\Models;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\TukosFramework as Tfk;

/*
 * Class to convert model values into view values and vice-versa
 */

class ModelsAndViews{


   /*
    * Returns conversion of $values according to $widgets['col'][$vieworObj] instructions:
    */
    public function convert($values, $widgets, $viewOrObj, $isMultiRows=false, $blankToNull=true, $format = false){
        if (empty($values)){
            return $values;
        }else{
            if ($isMultiRows){
                foreach ($values as &$value){
                    $value = $this->convertRow($value, $widgets, $viewOrObj, $format);
                }
            }else{
                $values = $this->convertRow($values, $widgets, $viewOrObj, $format);
            }
            if ($blankToNull){
                self::blankToNull($values, $isMultiRows);
            }
            return $values;
        }
    }
    public function convertRow($row, $widgets, $viewOrObj, $format = false){
        $cols = array_intersect(array_keys($row), array_keys($widgets));
        foreach ($cols as $col){
            if (isset($widgets[$col][$viewOrObj])){
                foreach ($widgets[$col][$viewOrObj] as $func => $params){
                    if (is_callable($func)){
                        $row[$col] = call_user_func_array($func, array_merge([$row[$col]], $params));
                    }else if(isset($params['class'])){
                        $method = [$params['class'], $func];
                        unset($params['class']);
                        foreach ($params as &$param){
                            if (is_string($param) && $param[0] === '@'){
                                $sourceCol = substr($param, 1);
                                if (isset($row[$sourceCol])){
                                    $param = $row[$sourceCol];
                                }
                            }
                        }
                        $row[$col] = call_user_func_array($method, array_merge([$row[$col]], $params));
                    }else{
                        $row = call_user_func_array([__NAMESPACE__ . '\ModelsAndViews', $func], array_merge([$row, $col], [$params]));            
                    }
                }
            }
            if ($format && isset($widgets[$col]['format'])){
                $row[$col] = $this->format($row[$col], $widgets[$col]['format']);
            }
        }
        return $row;
    }
    public static function fromTo(&$rows, $from, $to, $isMultiRows=false){
        if ($isMultiRows){
            foreach ($rows as $key => $row){
                foreach ($row as $id => $value){
                    if ($value === $from){
                        $rows[$key][$id] = to;
                    }
                }
            }
        }else{
            foreach ($rows as $id => $value){
                if ($value === $from){
                    $rows[$id] = $to;
                }
            }
        }
    }
    public static function blankToNull(&$rows, $isMultiRows=false){
        return ModelsAndViews::fromTo($rows, '', null, $isMultiRows);
    }


    public static function namedIdToId($values, $col){
        preg_match('/(\d+)\)$/', $values[$col], $matches);
        return $matches[1];
    }

    public static function namedId($values){
        return (isset($values['name']) ? $values['name'] : '')  . '(' . $values['id'] . ')';
    }

    public static function translate($values, $idCol, $translate){
        if(!empty($translate)){
            $func = key($translate);
            $method = [$translate[$func]['class'], $func];
            unset($translate[$func]['class']);
            if (is_array($values[$idCol])){
                $values[$idCol]['name'] = call_user_func_array($method, array_merge([$values[$idCol]['name']], $translate[$func]));
            }else{
                $values[$idCol]         = call_user_func_array($method, array_merge([$values[$idCol]], $translate[$func]));
            }
        }
        return $values;
    }
    public static function toUTC($values, $col){
        if (isset($values[$col])){
            $values[$col] = Dutl::toUTC($values[$col]);
        }
        return $values;
    }
    public static function fromUTC($values, $col){
        if (isset($values[$col])){
            $values[$col] = Dutl::fromUTC($values[$col]);
        }
        return $values;
    }

    public function format($value, $formatInfo){
        if ($formatInfo['type'] === 'date'){
            return date($this->user->dateFormat(), strtotime($value));
        }else if($formatInfo['type'] === 'tr'){
            $method = [$formatInfo['class'], 'tr'];
            return  call_user_func($method, $value);
        }
    }
}
?>
