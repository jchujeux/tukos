<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Objects\Sports\Sports;

class SessionsFeedbackV2 extends SessionsFeedbackVersion{
    function __construct(){
        parent::__construct();
        $this->formToSheetMapping = ['name' => 4, 'sport' => 5, 'duration' => 6, 'distance' => 7, 'elevationgain' => 8, 'sensations' => 9, 'mood' => 10, 'athletecomments' => 11, 'athleteweeklyfeeling' => 12];
        $this->storeToSheetMapping = ['name' => 4, 'sport' => 5, 'duration' => 6, 'distance' => 7, 'elevationgain' => 8, 'sensations' => 9, 'mood' => 10, 'athletecomments' => 11, 'athleteweeklyfeeling' => 12,
            'coachcomments' => 13, 'coachweeklycomments' => 14];
        $this->formObjectWidgets = ['name', 'sport', 'distance', 'elevationgain', 'sensations', 'mood', 'athletecomments', 'athleteweeklyfeeling'];
        $this->formCols = ['name', 'sport', 'duration', 'distance', 'elevationgain', 'sensations', 'mood', 'athletecomments', 'athleteweeklyfeeling', 'sportsman'];
        $this->sheetCols = ['name', 'sport', 'duration', 'distance', 'elevationgain', 'sensations', 'mood', 'athletecomments', 'athleteweeklyfeeling', 'coachcomments', 'coachweeklycomments'];
        $this->numberWidgets = ['distance', 'elevationgain'];
        $this->ratingWidgets = ['sensations', 'mood'];
        $this->row2LayoutWidgets = ['name', 'sport', 'duration', 'distance', 'elevationgain', 'sensations', 'mood'];
    }
    public function getFormDataWidgets(){
        parent::getFormDataWidgets();
        $this->dataWidgets['sport']['atts']['edit']['storeArgs']['data'] = Utl::idsNamesStore(['Running', 'Bicycle', 'Swimming', 'Bodybuilding'], $this->view->tr);
   /*
        foreach ($this->dataWidgets['sport']['atts']['edit']['storeArgs']['data'] as &$item){
            if (!empty($item['id'])){
                $item['label'] = '<img src="' . Tfk::publicDir . 'images/' . Sports::$sportImagesMap[strtolower($item['id'])] . '" alt="' . $item['name'] . '">';
            }else{
                $item['label'] = '';
            }
        }
   */
        $this->dataWidgets['sport']['atts']['edit']['mobileWidgetType'] = 'MobileStoreSelect';
        //$this->dataWidgets['sport']['atts']['edit']['labelAttr'] = 'label';
        return $this->dataWidgets;
    }
    public function sheetRowToX($workbook, $sheet, $row, $mapping){
        $values = parent::sheetRowToX($workbook, $sheet, $row, $mapping);
        if (($duration = Utl::getItem('duration', $values)) && !empty($duration)){
            $values['duration'] = $duration * 1440;
        }
        return $values;
    }
    public function xToSheetRow($values, $workbook, $sheet, $row, $mapping){
        if (($duration = Utl::getItem('duration', $values)) && !empty($duration)){
            $values['duration'] = $duration / 1440;
        }
        return parent::xToSheetRow($values, $workbook, $sheet, $row, $mapping);
    }
}
?>
