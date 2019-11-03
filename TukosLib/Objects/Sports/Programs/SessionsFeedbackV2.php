<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Objects\Sports\Sports;

class SessionsFeedbackV2 extends SessionsFeedbackVersion{
    function __construct(){
        parent::__construct();
        $this->tukosToSheetRowMapping = ['name' => 4, 'sport' => 5, 'duration' => 6, 'distance' => 7, 'elevationgain' => 8, 'sensations' => 9, 'mood' => 10, 'athletecomments' => 11, 'coachcomments' => 13];
        $this->tukosToSheetWeeklyMapping = ['athleteweeklyfeeling' => 12, 'coachweeklycomments' => 14];
        $this->formObjectWidgets = ['name', 'duration', 'sport', 'distance', 'elevationgain', 'perceivedeffort', 'sensations', 'mood', 'athletecomments', 'athleteweeklyfeeling', 'coachcomments', 'coachweeklycomments'];
        $this->sheetCols = $this->formObjectWidgets;
        //$this->formCols = array_merge($this->sheetCols, ['sportsman']);
        $this->formWeeklyCols = ['athleteweeklyfeeling', 'coachweeklycomments'];
        $this->hideIfEmptyWidgets = ['coachcomments', 'coachweeklycomments'];
        $this->numberWidgets = ['distance', 'elevationgain'];
        $this->ratingWidgets = ['sensations', 'mood'];
        $this->row2LayoutWidgets = ['name', 'sport', 'duration', 'distance', 'elevationgain', 'perceivedeffort',  'sensations', 'mood'];
    }
    public function getFormDataWidgets(){
        parent::getFormDataWidgets();
        $this->dataWidgets['sport']['atts']['edit']['storeArgs']['data'] = Utl::idsNamesStore(['running', 'bicycle', 'swimming', 'bodybuilding'], $this->view->tr);
        $this->dataWidgets['sport']['atts']['edit']['mobileWidgetType'] = 'MobileStoreSelect';
        return $this->dataWidgets;
    }
    public function sheetRowToX($workbook, $sheet, $row, $rowMapping, $weeklyRow, $weeklyMapping){
        $values = parent::sheetRowToX($workbook, $sheet, $row, $rowMapping, $weeklyRow, $weeklyMapping);
        if (($duration = Utl::getItem('duration', $values)) && !empty($duration)){
            $values['duration'] = round($duration * 1440, 5);
        }
        return $values;
    }
    public function xToSheetRow($values, $workbook, $sheet, $row, $rowMapping, $weeklyRow, $weeklyMapping){
        if (($duration = Utl::getItem('duration', $values)) && !empty($duration)){
            $values['duration'] = $duration / 1440;
        }
        return parent::xToSheetRow($values, $workbook, $sheet, $row, $rowMapping, $weeklyRow, $weeklyMapping);
    }
}
?>
