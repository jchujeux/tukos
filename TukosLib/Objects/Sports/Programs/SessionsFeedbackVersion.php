<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\TukosFramework as Tfk;

Abstract class SessionsFeedbackVersion {

    function __construct(){
        $this->view = Tfk::$registry->get('objectsStore')->objectView('sptsessions');
        $this->dataWidgets = $this->view->dataWidgets;
        $this->conversionCache = [];
    }
    public function getFormDataWidgets(){
        return $this->dataWidgets = array_merge(
            ['sportsman' => ViewUtils::textBox($this->view, 'name', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '100%']]]]), 
             'startdate' => ViewUtils::tukosDateBox($this->view, 'date', ['atts' => ['edit' => ['disabled' => true]]]),
             'duration'  => ViewUtils::minutesTextBox($this->view, 'duration', ['atts' => ['edit' => ['label' => $this->view->tr('Duration') . ' (hh:mn)']]])],
            array_intersect_key($this->dataWidgets, array_flip($this->formObjectWidgets()))
        );
    }
    public function formObjectWidgets(){
        return $this->formObjectWidgets;
    }
    public function formCols(){
        return $this->sheetCols;
    }
/*
    public function sheetCols(){
        return $this->sheetCols;
    }
*/
    public function numberWidgets(){
        return $this->numberWidgets;
    }
    public function ratingWidgets(){
        return $this->ratingWidgets;
    }
    public function row2LayoutWidgets(){
        return $this->row2LayoutWidgets;
    }
    public function sheetRowToX($workbook, $sheet, $row, $rowMapping, $weeklyRow, $weeklyMapping){
        foreach ($rowMapping as $key => $col){
            $values[$key] = $this->cellToTukos($workbook->getCellValue($sheet, $row, $col), $key);
        };
        foreach ($weeklyMapping as $key => $col){
            $values[$key] = $this->cellToTukos($workbook->getCellValue($sheet, $weeklyRow, $col), $key);
        };
        return $values;
    }
    public function sheetRowToForm($description){
        return $this->sheetRowToX($description->workbook, $description->sheet, $description->row, $this->tukosToSheetRowMapping, $this->mondayRow($description->row, $description->rowDate), $this->tukosToSheetWeeklyMapping);
    }
    public function sheetRowToStore($description){
        $values = $this->sheetRowToForm($description);
/*
        if (($duration = Utl::getItem('duration', $values)) && !empty($duration)){
            $values['duration'] = '[' . floatval($duration) . ',"minute"]';
        }
*/
        return $values;
    }
    public function xToSheetRow($values, $workbook, $sheet, $row, $rowMapping, $weeklyRow, $weeklyMapping){
        $cellsUpdated = 0;
        foreach ($values as $key => $value){
            if ($col = Utl::getItem($key, $rowMapping)){
                $cellRow = $row;
            }else if ($col = Utl::getItem($key, $weeklyMapping)){
                $cellRow = $weeklyRow;
            }else{
                break; 
            }
            $value = $this->tukosToCell($values[$key], $key);
            if ($value != $workbook->getCellValue($sheet, $cellRow, $col)){
                $workbook->setCellValue($sheet, $value, $cellRow, $col);
                $cellsUpdated += 1;
            }
        }
        return $cellsUpdated;
    }
    public function formToSheetRow($values, $description){
        return $this->xToSheetRow($values, $description->workbook, $description->sheet, $description->row, $this->tukosToSheetRowMapping, $this->mondayRow($description->row, $description->rowDate), $this->tukosToSheetWeeklyMapping);
    }
    public function storeToSheetRow($values, $description){
/*
        if ($duration = Utl::getItem('duration', $values)){
            $values['duration'] = Dutl::seconds($duration) / 60;
        }
*/
        return $this->formToSheetRow($values, $description);
    }
    public function mondayRow($row, $rowYmd){
        return $row - date('N', strtotime($rowYmd)) + 1;
    }
    public function tukosToCell($value, $widgetName){
        if ($this->dataWidgets[$widgetName]['type'] === 'storeSelect'){
            $this->setTranslatedStoreSelects();
            return Utl::findReplace($this->translatedStores[$widgetName], 'id', $value, 'name', $this->conversionCache, false, false);
        }else{
            return $value;
        }
    }
    public function cellToTukos($value, $widgetName){
        if ($this->dataWidgets[$widgetName]['type'] === 'storeSelect'){
            $leadingNumeric = substr($value, 0, strspn($value, "0123456789"));
            if (strlen($leadingNumeric) > 0){
                return $leadingNumeric;
            }else{
                $this->setTranslatedStoreSelects();
                return Utl::findReplace($this->translatedStores[$widgetName], 'name', $value, 'id', $this->conversionCache, false, false);
            }
        }else{
            return $value;
        }
    }
    public function setTranslatedStoreSelects(){
        if (!isset($this->translatedStores)){
            $this->translatedStores = [];
            foreach ($this->dataWidgets as $name => $widget){
                if ($widget['type'] === 'storeSelect'){
                    $this->translatedStores[$name] = $widget['atts']['edit']['storeArgs']['data'];
                }
            }
            $this->translatedStores = json_decode(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode($this->translatedStores)), true);
        }
    }
}
?>
