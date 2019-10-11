<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;

class SessionsFeedbackV2 extends SessionsFeedbackVersion{
    function __construct(){
        parent::__construct();
        $this->formToSheetMappping = ['name' => 4, 'sport' => 5, 'duration' => 6, 'distance' => 7, 'elevationgain' => 8, 'sensations' => 9, 'mood' => 10, 'athletecomments' => 11, 'athleteweeklyfeeling' => 12];
        $this->storeToSheetMapping = ['name' => 4, 'sport' => 5, 'duration' => 6, 'distance' => 7, 'elevationgain' => 8, 'sensations' => 9, 'mood' => 10, 'athletecomments' => 11, 'athleteweeklyfeeling' => 12,
            'coachcomments' => 13, 'coachweeklycomments' => 14];
        $this->formObjectWidgets = ['name', 'sport', 'distance', 'elevationgain', 'sensations', 'mood', 'athletecomments', 'athleteweeklyfeeling'];
        $this->formCols = ['name', 'sport', 'duration', 'distance', 'elevationgain', 'sensations', 'mood', 'athletecomments', 'athleteweeklyfeeling', 'sportsman'];
        $this->sheetCols = ['name', 'sport', 'duration', 'distance', 'elevationgain', 'sensations', 'mood', 'athletecomments', 'athleteweeklyfeeling', 'coachcomments', 'coachweeklycomments'];
        $this->numberWidgets = ['distance', 'elevationgain'];
        $this->row2LayoutWidgets = ['name', 'sport', 'duration', 'distance', 'elevationgain', 'sensations', 'mood'];
    }
    public function getFormDataWidgets(){
        parent::getFormDataWidgets();
        $this->dataWidgets['sport']['atts']['edit']['storeArgs']['data'] = Utl::idsNamesStore(['Running', 'Bicycle', 'Swimming', 'Bodybuilding'], $this->view->tr);
        return $this->dataWidgets;
    }
}
?>
