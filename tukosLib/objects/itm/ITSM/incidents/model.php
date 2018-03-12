<?php
namespace TukosLib\Objects\ITM\ITSM\Incidents;

use TukosLib\Objects\ITM\AbstractModel;
use TukosLib\Objects\ITM\ITM;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    //protected $progressOptions      = ['submitted', 'logging and categorization', 'resolution', 'on hold', 'resolved', 'closed'];
    protected $escalationLevelOptions = ['normal', 'escalated', 'major'];
    protected $initVals = ['notifiedvia' => 'tukos', 'callbackmethod' => 'tukos', 'escalationlevel' => 'normal', 'progress' => 'submitted'];
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'notifiedby'        => 'INT(11) DEFAULT NULL',
            'notifiedvia'       => "ENUM ('" . implode("','", ITM::$notifiedViaOptions) . "') ",
            'callbackmethod'    => "ENUM ('" . implode("','", ITM::$callbackOptions) . "') ",
            'urgency'           => "ENUM ('" . implode("','", ITM::$urgencyOptions) . "') ",
            'impact'            => "ENUM ('" . implode("','", ITM::$impactOptions) . "') ",
            'priority'          => "ENUM ('" . implode("','", ITM::$priorityOptions) . "') ",
            'escalationlevel'   => "ENUM ('" . implode("','", $this->escalationLevelOptions) . "') ",
            'category'          => "ENUM ('" . implode("','", ITM::$categoryOptions) . "') ",
            'progress'          => "ENUM ('" . implode("','", ITM::$incidentsProgressOptions) . "') ",
            'assignedto'        => 'INT(11) DEFAULT NULL',
        ];
        $keysDefinition = ' KEY (`notifiedby`, `notifiedvia`, `urgency`, `impact`, `priority`, `escalationlevel`, `category`, `progress`, `assignedto`)';

        parent::__construct(
            $objectName, $translator, 'itincidents',
            ['parentid' => ['itsvcdescs'], 'notifiedby' => ['people'], 'assignedto' => ['teams']],
            [], $colsDefinition, $keysDefinition, ['notifiedvia', 'callbackmethod', 'urgency', 'impact', 'priority', 'escalationlevel', 'category', 'progress'],
        	['custom', 'history']
        );
    }
    function initialize($init=[]){
        return parent::initialize(array_merge($this->initVals, ['notifiedby' => $this->user->peopleId()], $init));
    }

    public function getProgressChanged($atts){
        $svcDescsModel = Tfk::$registry->get('objectsStore')->objectModel('itsvcdescs');
        $item = $svcDescsModel->getOne(['where' => ['id' => $atts['where']['parentid']], 'cols' => ['incidentswf']], ['incidentswf' => []]);
        if (!empty($item)){
            $incidentsWf = $item['incidentswf'];
            $wfStep = Utl::array2D_Search_Strict($incidentsWf, 'progress', $atts['where']['progress']);
            return ['assignedto' => (isset($incidentsWf[$wfStep]['assignedto']) ? $incidentsWf[$wfStep]['assignedto'] : '')];
        }else{
            return [];
        }
    }


}
?>
