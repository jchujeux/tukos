<?php
namespace TukosLib\Objects\Sports\Plans;

class WorkoutsFeedbackV2 extends WorkoutsFeedbackVersion{
    function __construct(){
        parent::__construct();
        $this->formObjectWidgets = ['id', 'sportsman', 'startdate', 'starttime', 'name', 'duration', 'sport', 'distance', 'elevationgain', 'perceivedeffort', 'perceivedmechload', 'sensations', 'mood', 'athletecomments', 'coachcomments'];
        $this->formWeeklyCols = ['athleteweeklyfeeling', 'coachweeklycomments'];
        $this->hideIfEmptyWidgets = ['coachcomments', 'coachweeklycomments'];
        $this->numberWidgets = ['distance', 'elevationgain'];
        $this->ratingWidgets = ['sensations', 'perceivedeffort', 'perceivedmechload', 'mood'];
        $this->row2LayoutWidgets = ['name', 'sport', 'duration', 'distance', 'elevationgain'];
        $this->row3LayoutWidgets = ['perceivedeffort',  'perceivedmechload', 'sensations', 'mood'];
        $this->synchroWidgets = ['duration', 'distance', 'elevationgain'];
    }
}
?>
