<?php

namespace TukosLib\Objects\Views\Noview\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;

class GetTemplate extends AbstractViewModel{

   function get($query){
    	return ['data' => $this->duplicate($query['dupid'], 'objToStoreEdit')];
    }
}
?>
