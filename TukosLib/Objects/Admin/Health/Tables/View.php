<?php
namespace TukosLib\Objects\Admin\Health\Tables;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Table name');
        $customDataWidgets = [
            'name' => ['atts' => ['edit' =>  ['disabled' => true]]],
            'datehealthcheck'  => ViewUtils::timeStampDataWidget($this, 'Checkup date', ['atts' => ['edit' => ['disabled' => true]]]),
            'countinsertedids' => ViewUtils::textBox($this, 'Count inserted Ids', ['atts' => ['edit' =>  ['style' => ['width' => '9em'], 'disabled' => true], 'storeedit' => ['width' => 85]]]),
            'countupdatedids'  => ViewUtils::textBox($this, 'Count updated Ids' , ['atts' => ['edit' =>  ['style' => ['width' => '9em'], 'disabled' => true], 'storeedit' => ['width' => 85]]]),      
            'countdeletedids'  => ViewUtils::textBox($this, 'Count deleted Ids' , ['atts' => ['edit' =>  ['style' => ['width' => '9em'], 'disabled' => true], 'storeedit' => ['width' => 85]]]),
            'backuptype'       => ViewUtils::storeSelect('backup', $this, 'Backup type'),   
            'backupfilename'   => ViewUtils::textBox($this, 'Backup filename', ['atts' => ['edit' =>  ['style' => ['width' => '15em'], 'disabled' => true]]]),
        ];
        $this->customize($customDataWidgets);
    }
}
?>
