<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;

class SessionsFeedbackV1 extends SessionsFeedbackVersion{
    function __construct(){
        parent::__construct();
        $this->formToSheetMapping = ['name' => 4, 'duration' => 5, 'distance' => 6, 'elevationgain' => 7, 'feeling' => 8, 'athletecomments' => 9, 'athleteweeklyfeeling' => 10];
        $this->storeToSheetMapping = ['name' => 4, 'duration' => 5, 'distance' => 6, 'elevationgain' => 7, 'feeling' => 8, 'athletecomments' => 9, 'athleteweeklyfeeling' => 10, 'coachcomments' => 11, 'coachweeklycomments' => 12];
        $this->formObjectWidgets = ['name', 'distance', 'elevationgain', 'feeling', 'athletecomments', 'athleteweeklyfeeling'];
        $this->formCols = ['name', 'distance', 'elevationgain', 'feeling', 'athletecomments', 'athleteweeklyfeeling', 'duration', 'sportsman'];
        $this->sheetCols = ['name', 'distance', 'elevationgain', 'feeling', 'athletecomments', 'athleteweeklyfeeling', 'duration', 'coachcomments', 'coachweeklycomments'];
        $this->numberWidgets = ['distance', 'elevationgain'];
        $this->ratingWidgets = ['feeling'];
        $this->row2LayoutWidgets = ['name', 'duration', 'distance', 'elevationgain', 'feeling'];
    }
}
?>
