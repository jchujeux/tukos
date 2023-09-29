<?php
namespace TukosLib\Objects\Itm\Itsm\Incidents;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Itm\Itm;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    //protected $progressOptions      = ['submitted', 'logging and categorization', 'resolution', 'on hold', 'resolved', 'closed'];
    protected $escalationLevelOptions = ['normal', 'escalated', 'major'];
    protected $initVals = ['notifiedvia' => 'tukos', 'callbackmethod' => 'tukos', 'urgency' => 'Medium', 'impact' => 'Medium', 'escalationlevel' => 'normal', 'progress' => 'submitted'];
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'notifiedby'        => 'INT(11) DEFAULT NULL',
            'notifiedvia'       => "ENUM ('" . implode("','", Itm::$notifiedViaOptions) . "')",
            'callbackmethod'    => "ENUM ('" . implode("','", Itm::$callbackOptions) . "')",
            'urgency'           => "ENUM ('" . implode("','", Itm::$urgencyOptions) . "')",
            'impact'            => "ENUM ('" . implode("','", Itm::$impactOptions) . "')",
            'priority'          => "ENUM ('" . implode("','", Itm::$priorityOptions) . "')",
            'escalationlevel'   => "ENUM ('" . implode("','", $this->escalationLevelOptions) . "')",
            'category'          => "ENUM ('" . implode("','", Itm::$categoryOptions) . "')",
            'progress'          => "ENUM ('" . implode("','", Itm::$incidentsProgressOptions) . "')",
            'assignedto'        => 'INT(11) DEFAULT NULL',
        ];

        parent::__construct(
            $objectName, $translator, 'itincidents',
            ['parentid' => ['itsvcdescs'], 'notifiedby' => ['people'], 'assignedto' => ['teams']],
            [], $colsDefinition, [['notifiedby', 'assignedto']], ['notifiedvia', 'callbackmethod', 'urgency', 'impact', 'priority', 'escalationlevel', 'category', 'progress'],
        	['custom', 'history']
        );
    }
    function initialize($init=[]){
        return parent::initialize(array_merge($this->initVals, ['notifiedby' => $this->user->peopleId()], $init));
    }

    public function getProgressChanged($query){
        $svcDescsModel = Tfk::$registry->get('objectsStore')->objectModel('itsvcdescs');
        $item = $svcDescsModel->getOne(['where' => ['id' => $query['parentid']], 'cols' => ['incidentswf']], ['incidentswf' => []]);
        if (!empty($item)){
            $incidentsWf = $item['incidentswf'];
            $wfStep = Utl::array2D_Search_Strict($incidentsWf, 'progress', $query['progress']);
            return ['data' => ['assignedto' => (isset($incidentsWf[$wfStep]['assignedto']) ? $incidentsWf[$wfStep]['assignedto'] : '')]];
        }else{
            return [];
        }
    }


}
?>
