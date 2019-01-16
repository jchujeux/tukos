<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Itm\Itsm\Slas;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');

        $customDataWidgets = [
            'deliverymgr' => ViewUtils::objectSelectMulti('deliverymgr', $this, 'DeliveryMgr'),
            'customerrep' => ViewUtils::objectSelectMulti('customerrep', $this, 'CustomerRep'),
            'startdate'   => ['type' => 'tukosDateBox',  'atts' => ['edit' =>  ['title' => $this->tr('Start date')]],],
            'enddate'     => ['type' => 'tukosDateBox',  'atts' => ['edit' =>  ['title' => $this->tr('End date')]],], 
            'itsystem'    => ViewUtils::objectSelectMulti('itsystem', $this, 'Scope'),
        ];

        $subObjects['supportgroups']   = [
            'object' => 'teams',          'atts' => ['title' => $this->tr('Support groups')], 'filters'   => ['parentid' => '@id'], 'allDescendants' => true
        ];
        $subObjects['calendarperiods'] = ['atts' => ['title' => $this->tr('Service Time Schedules')], 'filters'   => ['parentid' => '@id'], 'allDescendants' => true];
        $subObjects['itslatargets']    = ['atts' => ['title' => $this->tr('SLA Targets')], 'filters'   => ['parentid' => '@id'], 'allDescendants' => true];
        $this->customize($customDataWidgets, $subObjects);
    }
}
?>
