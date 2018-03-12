<?php

namespace TukosLib\Objects\Views\Pane\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Objects\Views\Models\Save as SaveModel;
use TukosLib\Objects\Views\Models\Delete as DeleteModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Save extends SaveModel {

    function save(){
        $valuesToSave = $this->dialogue->getValues();
        $saveOneExisting = $this->modelSaveOneExisting;
        $updated = $this->model->$saveOneExisting($valuesToSave);
        if ($updated){
            $idSaved = $updated['id'];
            Feedback::add(['objectUpdated' => $idSaved]);
            return $idSaved;
        }else{
            Feedback::add(['noChange' => $valuesToSave['id']]);
            return false;
        }
    }

}
?>
