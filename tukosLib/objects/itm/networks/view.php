<?php
/**
 *
 * class for viewing methods and properties for the Networks model object
 */
namespace TukosLib\Objects\ITM\Networks; 

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Owner organization', 'Description');

        $customDataWidgets = [
            'iprange'      => ['type' => 'textArea'    ,  'atts' => ['edit' =>  ['title' => $this->tr('iprange'), 'colspan' => 2]]],
            'signaturecmd' => ['type' => 'textArea'    ,  'atts' => ['edit' =>  ['title' => $this->tr('signaturecmd'), 'colspan' => 2]]],
            'signature'    => ['type' => 'textArea'    ,  'atts' => ['edit' =>  ['title' => $this->tr('signature'), 'colspan' => 2]]],
        ];

        $subObjects['connexions']  = ['atts' => ['title'     => $this->tr('Connexions')]        , 'filters'   => ['parentid' => '@id'], 'allDescendants' => true];
        $subObjects['scripts']     = ['atts' => ['title'     => $this->tr('Associated scripts')], 'filters'   => ['parentid' => '@id'], 'allDescendants' => true];

        $this->customize($customDataWidgets, $subObjects);
    }    


}
?>
