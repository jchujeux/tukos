<?php

namespace TukosLib\Objects\Wine\Stock\Views\Overview;

use TukosLib\Objects\Views\Overview\View as ActionView;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends ActionView{

    function __construct($actionController){

        parent::__construct($actionController);
        $this->view->dataWidgets = array_merge($this->view->dataWidgets, [
             'sumquantity'   => ViewUtils::textBox($this->view, 'total quantities',['atts' => ['edit' => ['style' => ['width' => '5em', 'disabled' => true]]]]),
                      'BT'   => ViewUtils::textBox($this->view, 'Bottles'         ,['atts' => ['edit' => ['style' => ['width' => '5em', 'disabled' => true]]]]),
                      'MG'   => ViewUtils::textBox($this->view, 'Magnums'         ,['atts' => ['edit' => ['style' => ['width' => '5em', 'disabled' => true]]]]),
                      'DM'   => ViewUtils::textBox($this->view, 'Double Magnums'  ,['atts' => ['edit' => ['style' => ['width' => '5em', 'disabled' => true]]]]),
                      'JB'   => ViewUtils::textBox($this->view, 'Jeroboams'       ,['atts' => ['edit' => ['style' => ['width' => '5em', 'disabled' => true]]]]),
                      'MT'   => ViewUtils::textBox($this->view, 'Mathusalems'     ,['atts' => ['edit' => ['style' => ['width' => '5em', 'disabled' => true]]]]),
                      'BB'   => ViewUtils::textBox($this->view, 'Bibs'            ,['atts' => ['edit' => ['style' => ['width' => '5em', 'disabled' => true]]]]),
                      'CU'   => ViewUtils::textBox($this->view, 'Cubitainers'     ,['atts' => ['edit' => ['style' => ['width' => '5em', 'disabled' => true]]]]),        ]);
        $this->summaryLayout = [
            'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'label' => '<b>' . $this->view->tr('Summary'), 'labelWidth' => '20%'],
            'widgets' => ['totalrecords', 'filteredrecords', 'sumquantity', 'BT', 'MG', 'DM', 'JB', 'MT', 'BB', 'CU'],
        ];
        $this->dataElements = ['overview', 'totalrecords', 'filteredrecords', 'sumquantity', 'BT', 'MG', 'DM', 'JB', 'MT', 'BB', 'CU'];
    }
}
?>
