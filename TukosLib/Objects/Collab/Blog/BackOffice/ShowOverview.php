<?php
namespace TukosLib\Objects\Collab\Blog\BackOffice;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class ShowOverview extends ObjectTranslator{
    function __construct($query){
        parent::__construct('blog');
        $this->objectsStore     = Tfk::$registry->get('objectsStore');
        $this->view  = $this->objectsStore->objectView('blog');
        $this->dataWidgets = ['overview' => ViewUtils::OnDemandGrid($this->view, 'postsoverview', ['comments' => ['label' => 'contentagain']], 
            ['atts' => ['edit' => ['storeArgs' => ['object' => 'BackOffice', 'view' => 'NoView', 'mode' => 'Pane', 'action' => 'Get', 'params' => ['actionModel' => 'GetItems', 'object' => 'Blog', 'form' => 'GetOverviewItems']]]]])];
        $this->dataLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert', 'showLabels' => false, 'spacing' => 0, 'widgetCellStyle' => ['backgroundColor' => '#d0e9fc']/*, 'style' => ['tableLayout' => 'fixed'], 'resizeOnly' => true*/],
            'widgets' => ['overview']
        ];
    }
}
?>
