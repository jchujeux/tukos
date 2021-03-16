<?php
/**
 *
 * class for the navigation tukos object
 * This object is not currently intended ot store any items: its purpose is to display items from other objects in a hierarchical tree 
 */
namespace TukosLib\Objects\Admin\Users\Navigation;

use TukosLib\Objects\AbstractModel;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition = [];
        parent::__construct($objectName, $translator, 'navigation', ['parentid' => ['users']], [], $colsDefinition);
    }   
}
?>
