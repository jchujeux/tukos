<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Itm\Itsm\SvcDescriptions;

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
            'itsystem'    => ViewUtils::objectSelectMulti('itsystem', $this, 'IT system Scope', ['atts' => ['edit' => ['style' => ['width' => '250px']]]]),
            'supportgroups' => ViewUtils::JsonGrid($this, 'SupportGroups', [
                    'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                    'team'    => ViewUtils::objectSelect($this, 'Support group', 'teams', ['atts' => ['storeedit' => ['width' => 50]]]),
                    'period'  => ViewUtils::objectSelect($this, 'Time schedule', 'calendarsentries', ['atts' => ['storeedit' => ['width' => 50]]]),
                    'contact' => ViewUtils::storeSelect('callback', $this, 'Contact method', null, ['atts' => ['storeedit' => ['width' => 50]]]),
                ],
                ['atts' => ['edit' => ['colspan' => 4, 'style' => ['width' => '60%'], 'sort' => [['property' => 'rowId', 'descending' => false]]]]]
            ),
            'incidentssla' => ViewUtils::JsonGrid($this, 'IncidentsSLATargets', [
                    'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                    'priority' => ViewUtils::storeSelect('priority', $this, 'Priority'),
                    'sla' => ViewUtils::objectSelect($this, 'Description', 'itslatargets'),
                ],
                ['atts' => ['edit' => ['storeedit' => ['editorArgs' => ['dropdownFilters' => ['itsmprocess' => 'incident']]], 'sort' => [['property' => 'rowId', 'descending' => false]]]]]
            ),
            'incidentswf' => ViewUtils::JsonGrid($this, 'Incidents workflow', [
                    'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                    'progress'   => ViewUtils::storeSelect('incidentsProgress', $this, 'Progress'),
                    'assignedto' => ViewUtils::objectSelect($this, 'assignedto', 'teams'),
                    'notifyvia' => ViewUtils::storeSelect('callback', $this, 'Notify via'),
                ],
                ['atts' => ['edit' => ['sort' => [['property' => 'rowId', 'descending' => false]],]]]
            ),
        ];
        $this->customize($customDataWidgets, [], ['grid' => ['supportgroups', 'incidentssla', 'incidentswf']], ['supportgroups' => [], 'incidentssla' => [], 'incidentswf' => []]);
    }
}
?>
