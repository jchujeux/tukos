<?php

namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

trait SubObjectGet {

    private static function colsToSend($editModelGet, $subObject, $widgetName){
        $colsToSend = empty($subObject['sendOnHidden']) ? [] : $subObject['sendOnHidden'];
        $mayNotBeSent = array_diff($subObject['getCols'], $colsToSend);
    	$customElements = $editModelGet->getElementsCustomization();
        foreach ($mayNotBeSent as $col){
            if (!isset($customElements[$widgetName]['atts']['columns'][$col]['hidden']) || !$customElements[$widgetName]['atts']['columns'][$col]['hidden']){
                $colsToSend[] = $col;
            }
        }
        return $colsToSend;
    }
    private static function setQuery($filters, $value, $contextPathId){
        $where = $contextPathId ? ['contextpathid' => $contextPathId] : [];
        $join = [];
        $substitute = function($filterValue) use ($value){
            if (is_string($filterValue) && $pos = strpos($filterValue, '@') !== false){
                switch ($pos){
                    case 0:
                        $filterValue = Utl::getItem(substr($filterValue, 1), $value, '');
                        break;
                    default:
                        $filterValue = preg_replace_callback("/@(\w+)/", function($matches) use ($value) {
                            return Utl::getItem($matches[1], $value, 0);}, $filterValue);
                }
            }
    		return $filterValue;
        };
        foreach ($filters as $col => $filter){
            if (is_string($col) && $col[0] === '&'){
            	break;
            }else if (is_array($filter)){
                $filter = Utl::map_array_recursive($filter, $substitute);
                reset($filter);
                if (key($filter) === 'tukosJoin'){
                    $join[] = $filter['tukosJoin'];
                }else{
                    $where[$col] = $filter;
                }
            }else{
            	if ($filter[0] === '@'){
            		$filterTarget = substr($filter, 1);
            		if (empty($value[$filterTarget])){//the target col value in the parent object is not defined => can't identify its descendants and subObject itself is undefined
            			$where[$col] = -1;//to ensure no row is returned
            		}else{
            			$where[$col] = $value[$filterTarget];
            		}
            	}else{
            		$where[$col] = $filter;
            	}
            }
        }
        return empty($join) ? ['where' => $where] : ['tukosJoin' => $join, 'where' => $where];
    }

    
}
?>
