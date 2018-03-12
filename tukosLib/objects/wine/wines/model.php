<?php
/**
 *
 * class for the wines tukos object, i.e; the wines brand-domain description
 */
namespace TukosLib\Objects\Wine\Wines;

use TukosLib\Objects\Wine\Wine;
use TukosLib\Objects\Wine\AbstractModel;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition = [ 'cuvee'         => 'VARCHAR(80)  DEFAULT NULL',
                            'grading'       => "ENUM ('" . implode("','", Wine::$gradingOptions) . "')",
                            'bottledby'     => 'INT(11)',
                            'grape'         => "ENUM ('" . implode("','", Wine::$grapeOptions) . "')",
                            'category'      => "ENUM ('" . implode("','", Wine::$categoryOptions) . "')",
                            'color'         => "ENUM ('" . implode("','", Wine::$colorOptions) . "')",
                            'sugar'         => "ENUM ('" . implode("','", Wine::$sugarOptions) . "')",
        ];
        parent::__construct($objectName, $translator, 'wines', ['parentid' => ['wineappellations'], 'bottledby' => ['organizations']], [], $colsDefinition, '', ['grading', 'grape', 'category', 'color', 'sugar']);
    }
}
?>
