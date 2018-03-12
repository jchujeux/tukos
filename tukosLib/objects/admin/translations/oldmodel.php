<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Admin\Translations;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Directory;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition = ['setname' => "VARCHAR(255) ",
                           'en_us'   => "VARCHAR(255) ",
                           'fr_fr'   => "VARCHAR(255) ",
                           'es_es'   => "VARCHAR(255) ",
        ];
        parent::__construct(
            $objectName, $translator, 'translations',
            ['parentid' => ['contexts']], 
            [], $colsDefinition, 'KEY (`setname`)', ['setname']
        );
        $this->setNameOptions = array_merge(['tukosApp', 'tukosLib', 'countrycodes'], Directory::getDomains());
    }
}
?>
