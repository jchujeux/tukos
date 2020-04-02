<?php
/**
 *
 * class for the wine input tukos object, i.e; the wines entered into the winestock
 */
namespace TukosLib\Objects\Wine\Cellars;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'winecellars', ['parentid' => ['people']], [], [], '');
    }

    function processOne($query){
        $objectsStore = Tfk::$registry->get('objectsStore');

        $wineInputsModel = $objectsStore->objectModel('wineinputs');
        $idsToProcess = array_column($wineInputsModel->getAll(['where' => $this->user->filter(['parentid' => $query['id'], 'status' => 'PENDING'], $wineInputsModel->objectName), 'cols' => ['id']]), 'id');
        $wineInputsModel->process($idsToProcess);

        $wineOutputsModel = $objectsStore->objectModel('wineoutputs');
        $idsToProcess = array_column($wineOutputsModel->getAll(['where' => $this->user->filter(['parentid' => $query['id'], 'status' => 'PENDING'], $wineOutputsModel->objectName), 'cols' => ['id']]), 'id');
        $wineOutputsModel->process($idsToProcess);
        return [];
    }
}
?>
