<?php
namespace TukosLib\Objects\Collab\Organizations;

use TukosLib\Objects\AbstractModel;

class Model extends AbstractModel {
    protected $segmentOptions = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'MULTI'];
    function __construct($objectName, $translator=null){
    
        $colsDefinition = ['segment' =>  'VARCHAR(50)  DEFAULT NULL',
                              'logo' =>  'VARCHAR(150)  DEFAULT NULL',];
        parent::__construct($objectName, $translator, 'organizations', ['parentid' => ['organizations']], [], $colsDefinition, [], ['segment']);
    }
}
?>
