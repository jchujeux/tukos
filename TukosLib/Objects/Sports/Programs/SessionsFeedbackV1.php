<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;

class SessionsFeedbackV1 extends SessionsFeedbackVersion{
    function __construct(){
        parent::__construct();
        $this->tukosToSheetRowMapping = ['name' => 4, 'duration' => 5, 'distance' => 6, 'elevationgain' => 7, 'feeling' => 8, 'athletecomments' => 9, 'coachcomments' => 11];
        $this->tukosToSheetWeeklyMapping = ['athleteweeklyfeeling' => 10, 'coachweeklycomments' => 12];
        $this->formObjectWidgets = ['name', 'duration', 'distance', 'elevationgain', 'feeling', 'athletecomments', 'athleteweeklyfeeling', 'coachcomments', 'coachweeklycomments'];
        $this->sheetCols = $this->formObjectWidgets;
        //$this->formCols = array_merge($this->sheetCols, ['sportsman']);
        $this->formWeeklyCols = ['athleteweeklyfeeling', 'coachweeklycomments'];
        $this->hideIfEmptyWidgets = ['coachcomments', 'coachweeklycomments'];
        $this->numberWidgets = ['distance', 'elevationgain'];
        $this->ratingWidgets = ['feeling'];
        $this->row2LayoutWidgets = ['name', 'duration', 'distance', 'elevationgain', 'feeling'];
    }
}
?>
