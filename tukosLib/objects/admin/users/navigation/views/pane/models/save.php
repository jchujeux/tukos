<?php
namespace TukosLib\Objects\Admin\Users\Navigation\Views\Pane\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Objects\StoreUtilities as SUtl;

class Save extends AbstractViewModel {
	function save(){
		$parentIds = $this->dialogue->getValues();
		$ids = array_keys($parentIds);
		$theQuery = 'UPDATE tukos ' . 
						'SET parentid = case id ' . implode(' ', array_map(function($id, $parentId){return 'WHEN ' . $id . ' THEN ' . $parentId;}, $ids, $parentIds)) . ' ELSE parentid END, ' .
							'updated = "' . date('Y-m-d H:i:s') . '", updator = ' . $this->user->id() .
					   ' WHERE id in (' . implode(',', $ids) . ')';
		SUtl::$store->query($theQuery);
		return [];
	}
}
?>