<?php

namespace TukosLib\Objects\Views\Pane;

use TukosLib\Objects\Views\Edit\SubObjects;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View {

    function __construct($actionController){
        $this->view     = $actionController->view;
        $this->request  = $actionController->request;
    }
    function Content($atts = []){
        $paneName = $this->request['pane'];
        $defAtts = $this->view->paneWidgets[$paneName];
        if (!empty($defAtts['subObjects'])){
        	subObjects::addWidgets($defAtts['subObjects'], $this->view);
        	$defAtts['widgetsDescription'] = array_merge($defAtts['widgetsDescription'], $this->view->widgetsDescription(array_keys($defAtts['subObjects'])));
        }
        return  Utl::array_merge_recursive_replace($defAtts, $atts);
    } 
}
?>
