<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Admin\ObjRelations;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    protected $relationsOptions = ["is a component of", "is associated with", "uses", "is a new version of", "will be replaced by ", "belongsTo"];
    protected $_colsDefinition = ['relatedid'     =>  "INT(11) NOT NULL"];
    protected $_colsIndexes =   [['relatedid']];
    
    function __construct($objectName, $translator=null){
        $user  = Tfk::$registry->get('user');
        $store  = Tfk::$registry->get('store');
        $relationObjects = array_intersect($user->allowedModules(), $store->tableList());
        parent::__construct($objectName, $translator, 'objrelations', ['parentid' => $relationObjects, 'relatedid'=> $relationObjects], [], $this->_colsDefinition, $this->_colsIndexes);
    }
}
?>
