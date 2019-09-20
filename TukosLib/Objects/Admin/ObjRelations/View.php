<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Admin\ObjRelations;

use TukosLib\Objects\AbstractView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Relating object', 'Relation');
        $customDataWidgets = [
            'name'      => ViewUtils::storeSelect('relations', $this, 'Relation', null, ['atts' => ['edit' => ['required' => true]]]),
            'relatedid' => ViewUtils::objectSelectMulti('relatedid', $this, 'Related object'),
        ];
        $this->customize($customDataWidgets);
    }    
}
?>
