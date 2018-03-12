<?php
namespace TukosLib\Objects\Views\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Save extends AbstractViewModel {

    function __construct($controller, $params=[]){
        parent::__construct($controller, $params);
        $this->modelSaveOneNew  = (empty($params['saveOneNew'])  ? 'insertExtended' : $params['saveOneNew']);
        $this->modelSaveOneExisting  = (empty($params['saveOneExisting'])  ? 'updateOneExtended' : $params['saveOneExisting']);
    }
    function saveOne($valuesToSave, $viewToModel){
        $valuesToSave = $this->viewToModel($valuesToSave, $viewToModel, false);
        if(!isset($valuesToSave['id'])){//is a new record
            $saveOneNew = $this->modelSaveOneNew;
            $new = $this->model->$saveOneNew($valuesToSave, false);
            if ($new){
                Feedback::add([$this->view->tr('newobjectid') => $new['id']]);
                return $new['id'];
            }else{
                Feedback::add($this->view->tr('objectNotCreated'));
                return false;
            }                
        }else{//update existing record
                $saveOneExisting = $this->modelSaveOneExisting;
                $updated = $this->model->$saveOneExisting($valuesToSave, []);
                if ($updated){
                    $idSaved = $updated['id'];
                    Feedback::add([$this->view->tr('objectUpdated') => $idSaved]);
                    return $idSaved;
                }else{
                    Feedback::add([$this->view->tr('noChange') => $valuesToSave['id']]);
                    return $updated;
                }
        }
    }
}
?>
