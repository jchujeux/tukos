<?php
/**
 *
 * class for the tasks tukos object, i.e. tbd
 */
namespace TukosLib\Objects\Collab\Tasks;

use TukosLib\Objects\AbstractModel;

class Model extends AbstractModel {
    protected  $initVals = ['completed' => 0];
    function __construct($objectName, $translator=null){
        $colsDefinition =  ['responsible'   =>  'INT(11) DEFAULT NULL',
                            'accountable'   =>  'INT(11) DEFAULT NULL',
                            'consulted'     =>  'INT(11) DEFAULT NULL',
                            'informed'      =>  'INT(11) DEFAULT NULL',
                            'start'         =>  'date NULL DEFAULT NULL',
                            'end'           =>  'date NULL DEFAULT NULL',
                            'mendays'       =>  'FLOAT DEFAULT NULL',
                            'completed'     =>  'FLOAT DEFAULT NULL',];
        $keysDefinition = ' KEY (`responsible`), KEY (`accountable`), KEY (`consulted`), KEY (`informed`)';

        parent::__construct(
            $objectName, $translator, 'tasks', 
            ['parentid' => ['users', 'people', 'organizations', 'tasks', 'itincidents'], 'responsible' => ['people'], 'accountable' => ['people'], 'consulted' => ['people'], 'informed' => ['people']],
             [], $colsDefinition, $keysDefinition);
    }
    function initialize($init=[]){
        return parent::initialize(array_merge($this->initVals, $init));
    }
}
?>
