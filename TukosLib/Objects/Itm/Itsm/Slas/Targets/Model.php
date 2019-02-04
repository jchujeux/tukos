<?php
namespace TukosLib\Objects\Itm\Itsm\Slas\Targets;

use TukosLib\Objects\Itm\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\Itm\Itm;
use TukosLib\Collab\calendars\Entries;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
   
    function __construct($objectName, $translator=null){
        $this->indicatorValueOptions = Itm::$priorityOptions;
        $colsDefinition = [
            'itsmprocess'    => "ENUM ('" . implode("','", Itm::$ItsmProcessOptions) . "')",
            'indicator'      => "ENUM ('" . implode("','", Itm::$indicatorOptions) . "')",
            'indicatorvalue' => "VARCHAR(80) DEFAULT ''",
            'response'       => 'VARCHAR(80)  DEFAULT NULL',
            'resolution'     => 'VARCHAR(80)  DEFAULT NULL',
            'closure'        => 'VARCHAR(80)  DEFAULT NULL',
            'svcperiodsid'   => 'INT(11) DEFAULT NULL',
        ];

        parent::__construct(
            $objectName, $translator, 'itslatargets',
            ['parentid' => ['itsvcdescs'], 'svcperiodsid' => ['calendarsentries']],
            [], $colsDefinition, [], ['itsmprocess', 'indicator', 'indicatorvalue']
        );
    }
}
?>
