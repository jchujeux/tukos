<?php
namespace TukosLib\Objects\Sports\Programs;

class SessionsFeedbackV2 extends SessionsFeedbackVersion{
    function __construct(){
        parent::__construct();
        $this->formObjectWidgets = ['id', 'sportsman', 'startdate', 'starttime', 'sessionid', 'name', 'duration', 'sport', 'distance', 'elevationgain', 'perceivedeffort', 'sensations', 'mood', 'athletecomments', 'coachcomments'];
        $this->formWeeklyCols = ['athleteweeklyfeeling', 'coachweeklycomments'];
        $this->hideIfEmptyWidgets = ['coachcomments', 'coachweeklycomments'];
        $this->numberWidgets = ['distance', 'elevationgain'];
        $this->ratingWidgets = ['perceivedeffort', 'sensations', 'mood'];
        $this->row2LayoutWidgets = ['name', 'sport', 'duration', 'distance', 'elevationgain'];
        $this->row3LayoutWidgets = ['perceivedeffort',  'sensations', 'mood'];
        $this->synchroWidgets = [/*'name', 'sport', */'duration', 'distance', 'elevationgain'];
    }
}
?>
