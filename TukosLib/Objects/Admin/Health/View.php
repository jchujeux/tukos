<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Admin\Health;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Health check name');
        $customDataWidgets = [
            'datehealthcheck'   => ViewUtils::timeStampDataWidget($this, 'Checkup date', ['atts' => ['edit' => ['disabled' => true]]]),
            'nextid'            => ViewUtils::textBox($this, 'nextid'            , ['atts' => ['edit' =>  ['style' => ['width' => '9em'], 'disabled' => true], 'storeedit' => ['width' => 85]]]),
            'countinsertedids'  => ViewUtils::textBox($this, 'Count inserted Ids', ['atts' => ['edit' =>  ['style' => ['width' => '9em'], 'disabled' => true], 'storeedit' => ['width' => 85]]]),
            'countupdatedids'   => ViewUtils::textBox($this, 'Count updated Ids' , ['atts' => ['edit' =>  ['style' => ['width' => '9em'], 'disabled' => true], 'storeedit' => ['width' => 85]]]),      
            'countdeletedids'   => ViewUtils::textBox($this, 'Count deleted Ids' , ['atts' => ['edit' =>  ['style' => ['width' => '9em'], 'disabled' => true], 'storeedit' => ['width' => 85]]]),
        ];
        $this->customize($customDataWidgets);
    }    
}
?>
