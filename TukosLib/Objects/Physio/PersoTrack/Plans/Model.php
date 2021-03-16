<?php
namespace TukosLib\Objects\Physio\PersoTrack\Plans;

use TukosLib\Objects\AbstractModel;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'diagnostic' => 'longtext', 
            'symptomatology' => 'longtext',
            'recentactivity' => 'longtext',
            'objective' => 'longtext', 
            'exercises' => 'longtext',
            'protocol' => 'longtext',
            'torespect' => 'longtext'
        ];
        parent::__construct($objectName, $translator, 'physiopersoplans', ['parentid' => ['physiopatients']], ['exercises'], $colsDefinition, [], [], ['custom'], ['name', 'parentid']);
    }
}
?>
