<?php

namespace TukosLib\Objects\Views\Overview\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;


class GetColsWidgetDescription extends AbstractViewModel {
    
    
    public function get($query){
        $widgetsDescription = $this->view->widgetsDescription(array_diff($this->view->gridCols(), ['id', 'updator', 'updated', 'creator', 'created']), true);
        return ['widgetsDescription' => $widgetsDescription];
    }
}
?>
