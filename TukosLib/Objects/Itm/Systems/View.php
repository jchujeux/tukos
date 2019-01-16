<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Itm\Systems;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');

        $customDataWidgets = [
            'citype'   => ViewUtils::storeSelect('ciType', $this, 'CI type'),
            'version'  => ViewUtils::textBox($this, 'Version'),
            'status'   => ViewUtils::storeSelect('ciStatus', $this, 'Status'),
        ];

        $subObjects['itsystems']    = ['atts' => ['title'     => $this->tr('Sub systems')], 'filters'   => ['parentid' => '@id'], 'allDescendants' => true];
        $subObjects['objrelations'] = ['atts' => ['title'     => $this->tr('Related CIs')], 'filters'   => ['parentid' => '@id'], 'allDescendants' => true];
        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>
