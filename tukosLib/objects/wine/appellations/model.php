<?php
/**
 *
 * class for the wines tukos object, i.e; the wines brand-domain description
 */
namespace TukosLib\Objects\Wine\Appellations;

use TukosLib\Objects\Wine\AbstractModel;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition = [ //'region'        => 'VARCHAR(80)  DEFAULT NULL',    
                            'subdivision'   => 'VARCHAR(80)  DEFAULT NULL',
                            //'country'       => 'VARCHAR(80)  DEFAULT NULL',
                        ];
        parent::__construct($objectName, $translator, 'wineappellations', ['parentid' => ['wineregions']], [], $colsDefinition, '');
    }
}
?>
