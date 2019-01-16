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

    private static function filterParentCols($filters){
        $filterParentCols = [];
        foreach ($filters as $col => $filter){
            if (is_string($filter) && $filter[0] === '@'){
                $filterParentCols[] = substr($filter, 1);
            }
        }
        return $filterParentCols;
    }

    private static function setQuery($filters, $value, $contextPathId){
        $query = $contextPathId ? ['contextpathid' => $contextPathId] : [];
        $substituteCharacter = '@';
        $substitute = function($filterValue) use ($substituteCharacter, $value){
    		if (is_string($filterValue) && $filterValue[0] === $substituteCharacter){
        		$filterTarget = substr($filterValue, 1);
    			if (array_key_exists($filterTarget, $value)){
    				$filterValue = $value[$filterTarget];
    			}else{
    				$filterValue = '';
    			}
    		}
    		return $filterValue;
        };
        foreach ($filters as $col => $filter){
            if (is_string($col) && $col[0] === '&'){
            	break;
            }else if (is_array($filter)){
            	//$query[$col] =  self::substitute($filter, $value);
            	$query[$col] = Utl::map_array_recursive($filter, $substitute);
            }else{
            	if ($filter[0] === $substituteCharacter){
            		$filterTarget = substr($filter, 1);
            		if (empty($value[$filterTarget])){//the target col value in the parent object is not defined => can't identify its descendants and subObject itself is undefined
            			$query[$col] = -1;//to ensure no row is returned
            		}else{
            			$query[$col] = $value[$filterTarget];
            		}
            	}else{
            		$query[$col] = $filter;
            	}
            }
        }
        return $query;
    }

    
}
?>
