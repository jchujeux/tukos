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
            'widgets' => ['id', 'parentid', 'name', 'password', 'rights', 'language', 'environment', 'targetdb', 'tukosorganization', 'dropboxaccesstoken', 'dropboxbackofficeaccess', 'googletranslationaccesskey', 'enableoffline']
        ];
        $this->dataLayout['contents']['rowcomments'] = [
            'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['10%', '10%', '25%', '10%', '20%', '25%'], 'widgetCellStyle' => ['verticalAlign' => 'top']],      
            'widgets' => ['modules', 'restrictedmodules', 'customviewids', 'customcontexts', 'pagecustom', 'comments'],
        ];
    }
}
?>
