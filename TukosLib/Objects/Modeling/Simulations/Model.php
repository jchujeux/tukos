<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Modeling\Simulations;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'dimension' => 'TINYINT DEFAULT NULL',
            'ndof' => 'TINYINT DEFAULT NULL', 
            'dofnames' => 'longtext DEFAULT NULL',
            'boundariesconstraints' => 'longtext DEFAULT NULL',
            'nodalconstraints' => 'longtext DEFAULT NULL',
            'boundariesrhs' => 'longtext DEFAULT NULL',
            'nodalrhs' => 'longtext DEFAULT NULL',
            'nodalsolution' => 'longtext DEFAULT NULL',
            'linearity' => 'VARCHAR(128) DEFAULT NULL',
            'nonlinearoptions' => 'VARCHAR(128) DEFAULT NULL',
            'timedependency' => 'VARCHAR(128) DEFAULT NULL',
            'meshid' => 'MEDIUMINT DEFAULT NULL', 
            'properties' => 'VARCHAR(128) DEFAULT NULL',
            'groups' => 'longtext DEFAULT NULL',
            'gmeshdiagram' => 'longtext DEFAULT NULL',
        ];
        parent::__construct($objectName, $translator, 'mdlsimulations', ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects(), 'meshid' => ['mdlmeshes']], ['dofnames', 'boundariesconstraints', 'nodalconstraints', 'boundariesrhs', 'nodalrhs', 'nodalsolution', 'groups'], $colsDefinition, [], [], ['custom']);
        $this->jsonColsIdCols = ['groups' => ['materialId']];
    }
}
?>
