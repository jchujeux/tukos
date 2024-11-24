<?php

namespace TukosLib\Objects\Admin\Users\CustomViews\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){

        parent::__construct($actionController);

        $this->dataLayout['contents']['row1'] = [
            'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 80],
            'widgets' => ['id', 'parentid', 'name', 'vobject', 'view', 'panemode']
        ];
        $this->dataLayout['contents']['rowcomments'] = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],      
            'widgets' => ['customization', 'comments'],
        ];
    }
}
?>
