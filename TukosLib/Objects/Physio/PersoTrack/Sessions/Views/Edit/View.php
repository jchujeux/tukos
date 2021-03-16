<?php

namespace TukosLib\Objects\Physio\PersoTrack\Sessions\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Utils\Utilities as Utl;


class View extends EditView{
    
    function __construct($actionController){
        parent::__construct($actionController);
        
        $customContents = [
            'row1' => [
                'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
                'widgets' => ['id', 'parentid', 'exercises', 'name', 'startdate', 'sessionid', 'duration']
            ],
            'row2' => [
                'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
                'widgets' => ['exerciseid', 'stress', 'series', 'repeats', 'extra', 'extra1'],
            ],
            'row3' => [
                'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
                'widgets' => ['painduring', 'painafter'/*, 'painnextday', 'mood', 'fatigue', 'otherexceptional'*/],
            ],
/*
            'row4' => [
                'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
                'widgets' => ['patientsessioncomments', 'therapistsessioncomments', 'patientweeklycomments', 'therapistweeklycomments']
            ]
*/
                ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowcomments', 'rowbottom', 'rowacl'], $this->dataLayout['contents']));
    }
}
?>
