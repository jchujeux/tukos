<?php

namespace TukosLib\Objects\Physio\PersoTrack\Plans\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Objects\Views\LocalActions;
use TukosLib\Utils\Utilities as Utl;

class View extends EditView{

	use LocalActions;
	
	function __construct($actionController){
       parent::__construct($actionController);

        $customContents = [
            'row1' => [
                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 80],
                'widgets' => ['id', 'parentid', 'name']
            ],
            'row2' => [
                'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 80],
                'widgets' => ['diagnostic', 'symptomatology', 'recentactivity', 'objective']
            ],
            'row3' => [
                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['60%', '40%'], 'widgetCellStyle' => ['verticalAlign' => 'top']],      
                'widgets' => [ 'exercises', 'exercisescatalog'],
            ],
            'rowcomments' => [
                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['34%', '33%', '33%'], 'widgetCellStyle' => ['verticalAlign' => 'top']],
                'widgets' => ['protocol', 'torespect', 'comments'],
            ],
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowbottom', 'rowacl'], $this->dataLayout['contents']));
        $this->actionWidgets['export']['atts']['clientDialogDescription'] = "tukos/objects/physio/persoTrackPlanExport";
	}
}
?>
