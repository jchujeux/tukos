<?php
namespace TukosLib\Objects\ITM\ITSM\SLAs\Targets;

use TukosLib\Objects\ITM\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\ITM\ITM;
use TukosLib\Collab\calendars\Entries;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
   
    function __construct($objectName, $translator=null){
        $this->indicatorValueOptions = ITM::$priorityOptions;
        $colsDefinition = [
            'itsmprocess'    => "ENUM ('" . implode("','", ITM::$ITSMProcessOptions) . "') ",
            'indicator'      => "ENUM ('" . implode("','", ITM::$indicatorOptions) . "') ",
            'indicatorvalue' => "VARCHAR(80) DEFAULT '' ",
            'response'       => 'VARCHAR(80)  DEFAULT NULL',
            'resolution'     => 'VARCHAR(80)  DEFAULT NULL',
            'closure'        => 'VARCHAR(80)  DEFAULT NULL',
            'svcperiodsid'   => 'INT(11) DEFAULT NULL',
        ];
        $keysDefinition = ' KEY (`itsmprocess`, `indicator`, `svcperiodsid`)';

        parent::__construct(
            $objectName, $translator, 'itslatargets',
            ['parentid' => ['itsvcdescs'], 'svcperiodsid' => ['calendarsentries']],
            [], $colsDefinition, $keysDefinition, ['itsmprocess', 'indicator', 'indicatorvalue']
        );
    }
}
?>
