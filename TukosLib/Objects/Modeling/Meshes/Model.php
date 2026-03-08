<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Modeling\Meshes;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'snodes' => 'longtext DEFAULT NULL',
            's1dgroups' => 'longtext DEFAULT NULL',
            's2dgroups' => 'longtext DEFAULT NULL',
            's3dgroups' => 'longtext DEFAULT NULL',
            'gnodes' => 'longtext DEFAULT NULL',
            'gboundaries' => 'longtext DEFAULT NULL',
            'g1dgroups' => 'longtext DEFAULT NULL',
            'g2dgroups' => 'longtext DEFAULT NULL',
            'g3dgroups' => 'longtext DEFAULT NULL',
            'smeshdiagram' => 'longtext DEFAULT NULL',
            'gmeshdiagram' => 'longtext DEFAULT NULL'
        ];
        parent::__construct($objectName, $translator, 'mdlmeshes', ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects()], ['snodes', 's1dgroups', 's2dgroups', 's3dgroups', 's1dboundarygroups',  'gnodes', 'gboundaries', 'g1dgroups', 'g2dgroups', 'g3dgroups', 'g1dboundarygroups'], 
            $colsDefinition, [], [], ['custom']);
    }
}
?>
