<?php

namespace TukosLib\Objects\Views\NoView\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;

class GetObjectModules extends AbstractViewModel{
  
	function get($query){
		$objects = Tfk::$registry->get('user')->allowedNativeObjects();
		foreach ($objects as $object){
            $modules[$object] = ['label' => $this->model->tr($object), 'object' => $object, 'dropdownFilters' => ['contextpathid' => '$tabContextId']];
        }
		return ['modules' => $modules];
	}
}
?>
