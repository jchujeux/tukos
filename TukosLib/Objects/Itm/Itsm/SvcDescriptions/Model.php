<?php
namespace TukosLib\Objects\Itm\Itsm\SvcDescriptions;

use TukosLib\Objects\Itm\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        
        $colsDefinition = [
            'deliverymgr'   => 'INT(11) DEFAULT NULL',
            'customerrep' => 'INT(11) DEFAULT NULL',
            'startdate' => 'date NULL DEFAULT NULL',
            'enddate'   => 'date NULL DEFAULT NULL',
            'itsystem'  => 'INT(11) DEFAULT NULL',
            'supportgroups'  => 'VARCHAR(2048)  DEFAULT NULL',
            'incidentssla'   => 'VARCHAR(2048)  DEFAULT NULL',
            'incidentswf' => 'VARCHAR(2048)  DEFAULT NULL',
        ];
        $keysDefinition = ' KEY (`deliverymgr`, `customerrep`, `itsystem`)';

        parent::__construct(
            $objectName, $translator, 'itsvcdescs',
            ['parentid' => ['organizations'], 'deliverymgr' => ['people'], 'customerrep' => ['people'], 'itsystem' => ['itsystems']],
            ['supportgroups', 'incidentssla', 'incidentswf'], $colsDefinition, $keysDefinition
        );
        $this->gridsIdCols =  ['supportgroups' => ['team', 'period'], 'incidentssla' => ['sla'], 'incidentswf' => ['assignedto'], ];
    }

    function initialize($init=[]){
        return parent::initialize(array_merge([
                'incidentssla' => [['rowId' => 1, 'priority' => 'critical'], ['rowId' => 2, 'priority' => 'high'], ['rowId' => 3, 'priority' => 'medium'], ['rowId' => 4, 'priority' => 'low'], ['rowId' => 5, 'priority' => 'verylow']], 
                'incidentswf' => [['rowId' => 1, 'progress' => 'submitted'], ['rowId' => 2, 'progress' => 'logging and categorization'], ['rowId' => 3, 'progress' => 'resolution'], ['rowId' => 4, 'progress' => 'on hold'], ['rowId' => 5, 'progress' =>'resolved'], ['rowId' => 6, 'progress' => 'closed']],
            ], 
            $init
        ));
    }

    public function supportGroup($id){
        return $this->getOne(['where' => ['id' => $id], 'cols' => ['sipportgroups']], ['supportgroups' => []])['supportgroups'];
    }
    public function sla($id){
        return $this->getOne(['where' => ['id' => $id], 'cols' => ['sla']], ['sla' => []])['sla'];
    }
    public function incidentsWf($id){
        return $this->getOne(['where' => ['id' => $id], 'cols' => ['incidentswf']], ['incidentswf' => []])['incidentswf'];
    }
}
?>
