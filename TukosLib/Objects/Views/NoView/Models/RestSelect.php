<?php

namespace TukosLib\Objects\Views\NoView\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;

class RestSelect extends AbstractViewModel{
  
	function get($query){
		$storeAtts = $query['storeatts'];
		if (Utl::getItem('one', $query)){// A single row is expected (example: JsonRest get function)
			$getOne = (isset($query['params']['getOne'])  ?  $query['params']['getOne'] : 'restGetOne');
			return ['item' => $this->model->$getOne($storeAtts)];
		}else{// An array of rows is expected (example: JsonREST query)
        	$getAll = (isset($query['params']['getAll'])  ?  $query['params']['getAll'] : 'restGetAll');
			$result = $this->model->$getAll($storeAtts);
        	return ['items' => $result, 'total' => count($result)];
		}
	}
}
?>
