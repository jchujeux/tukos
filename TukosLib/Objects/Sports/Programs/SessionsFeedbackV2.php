<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Objects\Sports\Sports;

class SessionsFeedbackV2 extends SessionsFeedbackVersion{
    function __construct(){
        parent::__construct();
        $this->formObjectWidgets = ['id', 'startdate', 'sessionid', 'name', 'duration', 'sport', 'distance', 'elevationgain', 'perceivedeffort', 'sensations', 'mood', 'athletecomments', 'athleteweeklyfeeling', 'coachcomments', 'coachweeklycomments'];
        $this->formWeeklyCols = ['athleteweeklyfeeling', 'coachweeklycomments'];
        $this->hideIfEmptyWidgets = ['coachcomments', 'coachweeklycomments'];
        $this->numberWidgets = ['distance', 'elevationgain'];
        $this->ratingWidgets = ['perceivedeffort', 'sensations', 'mood'];
        $this->row2LayoutWidgets = ['name', 'sport', 'duration', 'distance', 'elevationgain', 'perceivedeffort',  'sensations', 'mood'];
    }
}
?>
