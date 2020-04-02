<?php

namespace TukosLib\Objects\Physio\Protocols;

use TukosLib\Objects\Collab\Calendars\Model as CalendarsModel;
use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;
//use Google\Client as GoogleClient;

class Model extends CalendarsModel {
	function __construct($objectName, $translator=null){
        $colsDefinition = [
        		'sources' =>  'longtext',
        		'sessions' =>  'longtext',
        		'periodstart' => 'VARCHAR(20) DEFAULT NULL',
        		'periodend'   => 'VARCHAR(20) DEFAULT NULL'
        ];
        AbstractModel::__construct($objectName, $translator, 'physioprotocols', ['parentid' => ['physioprescriptions']], ['sources', 'sessions'], $colsDefinition, [], [], ['custom']);
        $this->gridsIdCols = array_merge($this->gridsIdCols, ['sources' => ['tukosparent']]);
    }
}
?>
