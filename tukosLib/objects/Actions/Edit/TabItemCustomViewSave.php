<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\Edit\Tab;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class TabItemCustomViewSave extends Tab{
    function response($query){
        $this->objectsStore->objectViewModel($this->controller, 'Edit', 'save', ['model' => $this->objectsStore->objectModel('customviews')])->save($query);
        return parent::response($query);
    }
}
?>
