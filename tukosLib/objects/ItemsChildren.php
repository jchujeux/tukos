<?php
/**
 *
 * Provide methods to deal with parent - child methods for object items
 */
namespace TukosLib\Objects;

use TukosLib\Utils\Utilities as Utl;

use TukosLib\TukosFramework as Tfk;

trait ItemsChildren {


    protected function addDescendants(&$values, $atts){
        unset($atts['where']['parentid']);
        $otherWhere = $atts['where'];
        $gottenIds      = array_column($values, 'id');
        $pureParentIds  = array_diff($gottenIds, array_unique(array_column($values, 'parentid')));
        $idsWithChildren= array_diff($gottenIds, $pureParentIds);//children for those ids were gotten via the initial getAll() => don't do it again
        $remainingIds   = $pureParentIds;
        $excludeIds = $idsWithChildren;
        while (count($remainingIds) > 0){
            $atts['where'][]    = ['col' => 'parentid', 'opr' =>     'IN', 'values' => $remainingIds];  
            if (!empty($excludeIds)){
                $atts['where'][]    = ['col' => 'id', 'opr' => 'NOT IN', 'values' => $excludeIds]; 
            } 
            $newDescendants     = $this->getAll($atts);
            if (!empty($newDescendants)){
                $idsWithChildren    = array_merge($idsWithChildren, array_unique(array_column($newDescendants, 'parentid')));
                $values             = array_merge($values, $newDescendants);
                $remainingIds       = array_unique(array_column($newDescendants, 'id'));
                $excludeIds         = [];
                $atts['where']      = $otherWhere; 
            }else{
                $remainingIds = [];
            }
        }
        if (!empty($idsWithChildren)){
            $idsWithChildren = array_flip($idsWithChildren);
            foreach ($values as $key => &$value){
                if (isset($idsWithChildren[$value['id']])){
                    $value['hasChildren'] = true;
                }else{
                    $value['hasChildren'] = false;
                }
            }
        }
    }

   /*
    * Removes children items from $values, i.e. those items which 'parentid' is an 'id' of $values, and sets the 'hasChildren' flag to true for the corresponding parent item
    */
    protected function extractChildren(&$values){
        $parentIds = array_column($values, 'parentid');
        $ids       = array_column($values, 'id');
        $childrenIds = array_intersect($parentIds, $ids);
        foreach ($values as &$value){
            $key = array_search($value['parentid'], $ids);
            if ($key !== false && isset($values[$key])){
                $values[$key]['hasChildren'] = true;
                unset($value);
            }
        }
    }
   
   /*
    * Sets the 'hasChildren' flag to true for each item in $values that has 'children' matching $atts conditions
    */
/*
    protected function setHasChildren(&$values, $atts, $extractChildren = true){
        if ($extractChildren){
            $this->extractChildren($values);
        }
        if (isset($atts['where']['parentid'])){//some items may have been filtered out which are children from an item in $values, so we need to look for more children into the store
            unset($atts['where']['parentid']);
            $idsToConsider = ($extractChildren 
                ? array_filter(array_column($values, 'id'), function($value){return empty($value['hasChildren']);})
                : array_column($values, 'id')
            );
            $atts['where'][] = ['col' => 'parentid', 'opr' =>     'IN', 'values' => $idsToConsider]; 
            $atts['cols']    = ['parentid']; 
            $atts['groupBy'] = ['parentid'];
            $idsWithChildren = array_column($this->getAll($atts), 'parentid');
            if (!empty($idsWithChildren)){
                $idsWithChildren = array_flip($idsWithChildren);
                foreach ($values as $key => &$value){
                    if (isset($idsWithChildren[$value['id']])){
                        $value['hasChildren'] = true;
                    }
                }
            }
        }
    }
*/
    protected function setHasChildren(&$values, $atts){
    	unset($atts['where']['parentid']);
    	$atts['where'][] = ['col' => 'parentid', 'opr' => 'IN', 'values' => array_column($values, 'id')];
    	$atts['cols'] = ['parentid'];
    	$atts['groupBy'] = ['parentid'];
    	$idsWithChildren = array_column($this->getAll($atts), 'parentid');
    	if (!empty($idsWithChildren)){
    	    $idsWithChildren = array_flip($idsWithChildren);
            foreach ($values as $key => &$value){
                if (isset($idsWithChildren[$value['id']])){
                    $value['hasChildren'] = true;
                }
            }
    	}
    }
}
?>
