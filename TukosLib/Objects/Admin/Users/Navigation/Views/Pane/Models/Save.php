<?php
namespace TukosLib\Objects\Admin\Users\Navigation\Views\Pane\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Objects\StoreUtilities as SUtl;

class Save extends AbstractViewModel {
	function save(){
		$parentIds = $this->dialogue->getValues();
		$itemsParentIds = [];
		$objectsParentIds = [];
		array_walk($parentIds, function($newParentId, $oldParentInfo) use (&$itemsParentIds, &$objectsParentIds) {
			$oldParentElts = explode('@', $oldParentInfo);
			if (count($oldParentElts) > 1){
				$objectsParentIds[] = ['object' => $oldParentElts[0], 'oldParentId' => $oldParentElts[1], 'newParentId' => $newParentId];
			}else{
				$itemsParentIds[$oldParentInfo] = $newParentId;
			}
		});
		if (!empty($itemsParentIds)){
			$ids = array_keys($itemsParentIds);
			$theQuery = 'UPDATE tukos ' .
					'SET parentid = case id ' . implode(' ', array_map(function($id, $parentId){return 'WHEN ' . $id . ' THEN ' . $parentId;}, $ids, $itemsParentIds)) . ' ELSE parentid END, ' .
					'updated = "' . date('Y-m-d H:i:s') . '", updator = ' . $this->user->id() .
					' WHERE id in (' . implode(',', $ids) . ')';
			SUtl::$store->query($theQuery);
		}
		if (!empty($objectsParentIds)){
			$objects = array_unique(array_column($objectsParentIds, 'object'));
			$ids = array_unique(array_column($objectsParentIds, 'oldParentId'));
			$theQuery = 'UPDATE tukos ' .
				'SET parentid = case ' . implode(' ', array_map(function($item){return 'WHEN (object ="' . $item['object'] . '" AND parentid = "' . $item['oldParentId'] . '") THEN ' . $item['newParentId'];}, $objectsParentIds)) .
				' ELSE parentid END, ' . 'updated = "' . date('Y-m-d H:i:s') . '", updator = ' . $this->user->id() .
				' WHERE object in ("' . implode('","', $objects) . '") AND parentid in (' . implode(',', $ids) . ')';
			SUtl::$store->query($theQuery);
		}
		return [];
	}
}
?>