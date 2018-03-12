<?php

namespace TukosLib\Objects\Admin\Users\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    function __construct($actionController){

        parent::__construct($actionController);

        $this->dataLayout['contents']['row1'] = [
            'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 80],
            'widgets' => ['id', 'parentid', 'name', 'password', 'rights', 'language', 'environment', 'targetdb']
        ];
        $this->dataLayout['contents']['row2'] = [
            'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['5%', '5%', '5%', '5%', '50%', '50%'], 'widgetCellStyle' => ['verticalAlign' => 'top']],      
            'widgets' => (isset($this->view->dataWidgets['worksheet']) ? ['modules', 'customviewids', 'customcontexts', 'pagecustom', 'worksheet', 'comments'] : ['modules', 'customviewids', 'customcontexts', 'pagecustom', 'comments']),
        ];
    }
}
?>
