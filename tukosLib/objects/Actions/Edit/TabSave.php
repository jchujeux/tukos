<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\Edit\Tab;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class TabSave extends Tab{
    function response($query){
        $this->objectsStore->objectViewModel($this->controller, 'Edit', 'Save')->save($query);
        return parent::response($query);
    }
}
?>
