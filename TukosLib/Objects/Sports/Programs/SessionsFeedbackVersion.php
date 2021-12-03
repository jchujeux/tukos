<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\TukosFramework as Tfk;

Abstract class SessionsFeedbackVersion {

    function __construct(){
        $this->view = Tfk::$registry->get('objectsStore')->objectView('sptsessions');
        //$this->dataWidgets = $this->view->dataWidgets;
    }
    public function getFormDataWidgets(){
        $dataWidgets = array_merge(
            array_intersect_key($this->view->dataWidgets, array_flip($this->formObjectWidgets())), ['sportsman' => ViewUtils::textBox($this->view, 'name', ['atts' => ['edit' => ['readonly' => true, 'style' => ['color' => 'grey', 'width' => '100%', 'fontWeight' => 'bolder']]]])]
        );
        $dataWidgets['id']['atts']['edit']['hidden'] = true;
        $dataWidgets['sport']['atts']['edit']['storeArgs']['data'] = Utl::idsNamesStore(['running', 'bicycle', 'swimming', 'bodybuilding'], $this->view->tr);
        return $dataWidgets;
    }
    public function formObjectWidgets(){
        return $this->formObjectWidgets;
    }
    public function formCols(){
        return $this->formObjectWidgets;
    }
    public function numberWidgets(){
        return $this->numberWidgets;
    }
    public function ratingWidgets(){
        return $this->ratingWidgets;
    }
    public function row2LayoutWidgets(){
        return $this->row2LayoutWidgets;
    }
    public function row3LayoutWidgets(){
        return $this->row3LayoutWidgets;
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
