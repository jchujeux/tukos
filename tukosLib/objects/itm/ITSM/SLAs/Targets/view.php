<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\ITM\ITSM\SLAs\Targets;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Associated SLA', 'Description');

        $customDataWidgets = [
            'itsmprocess'    => ViewUtils::storeSelect('ITSMProcess', $this, 'ITSMProcess'),
            'indicator'      => ViewUtils::storeSelect('indicator'  , $this, 'Indicator'),
            'indicatorvalue' => ViewUtils::storeSelect('indicatorValue', $this, 'Indicator value'),
            'response'       => ViewUtils::numberUnitBox('timeInterval', $this, 'Response target'),
            'resolution'     => ViewUtils::numberUnitBox('timeInterval', $this, 'Resolution target'),
            'closure'       => ViewUtils::numberUnitBox('timeInterval', $this, 'Closure target'),
            'svcperiodsid'  => ViewUtils::objectSelectMulti('svcperiodsid', $this, 'Service hours'),
        ];
        $this->customize($customDataWidgets);
    }
}
?>
