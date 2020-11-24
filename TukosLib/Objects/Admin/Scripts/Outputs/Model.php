<?php
namespace TukosLib\Objects\Admin\Scripts\Outputs;

use TukosLib\Objects\AbstractModel;
use TukosLib\TukosFramework as Tfk; 

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition =  ['output'    =>  'longtext  DEFAULT NULL ', 'errors'    =>  'longtext  DEFAULT NULL '];
        parent::__construct($objectName, $translator, 'scriptsoutputs', ['parentid' => ['users', 'scripts']], [], $colsDefinition, [], [], ['custom'], ['name', 'parentid']);
    }    
}
?>
