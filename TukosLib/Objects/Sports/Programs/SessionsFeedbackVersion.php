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
            ['sportsman'      => ViewUtils::textBox($this->view, 'name', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '100%']]]]), 'startdate'   => ViewUtils::tukosDateBox($this->view, 'date', ['atts' => ['edit' => ['disabled' => true]]]),
                'duration' => ViewUtils::minutesTextBox($this->view, 'duration', ['atts' => ['edit' => ['label' => $this->view->tr('Duration') . ' (hh:mn)']]])],
            array_intersect_key($this->dataWidgets, array_flip($this->formObjectWidgets()))
        );
    }
    public function formObjectWidgets(){
        return $this->formObjectWidgets;
    }
    public function formCols(){
        return $this->formCols;
    }
    public function sheetCols(){
        return $this->sheetCols;
    }
    public function numberWidgets(){
        return $this->numberWidgets;
    }
    public function row2LayoutWidgets(){
        return $this->row2LayoutWidgets;
    }
    public function sheetRowToX($workbook, $sheet, $row, $mapping){
        foreach ($mapping as $key => $col){
            $values[$key] = $this->cellToTukos($workbook->getCellValue($sheet, $row, $col), $key);
        };
        return $values;
    }
    public function sheetRowToForm($workbook, $sheet, $row){
        return self::sheetRowToX($workbook, $sheet, $row, $this->formToSheetMapping);
    }
    public function sheetRowToStore($workbook, $sheet, $row){
        $values =  self::sheetRowToX($workbook, $sheet, $row, $this->storeToSheetMapping);
        if (($duration = Utl::getItem('duration', $values)) && !empty($duration)){
            $values['duration'] = '[' . floatval($duration) . ',"minute"]';
        }
        return $values;
    }
    public function xToSheetRow($values, $workbook, $sheet, $row, $mapping){
        $cellsUpdated = 0;
        foreach ($values as $key => $value){
            $col = $mapping[$key];
            $value = $this->tukosToCell($values[$key], $key);
            if ($value != $workbook->getCellValue($sheet, $row, $col)){
                $workbook->setCellValue($sheet, $value, $row, $col);
                $cellsUpdated += 1;
            }
        }
        return $cellsUpdated;
    }
    public function storeToSheetRow($values, $workbook, $sheet, $row){
        if ($duration = Utl::getItem('duration', $values)){
            $values['duration'] = Dutl::seconds($duration) / 60;
        }
        return $this->xToSheetRow($values, $workbook, $sheet, $row, $this->storeToSheetMapping);
    }
    public function formToSheetRow($values, $workbook, $sheet, $row){
        return $this->xToSheetRow($values, $workbook, $sheet, $row, $this->formToSheetMapping);
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
