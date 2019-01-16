<?php
namespace TukosLib\Objects\Views\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Delete extends AbstractViewModel {

    function deleteOne($values){
        return $this->model->delete(['id' => $values['id']], $values);
    }

    function deleteMultiple($valuesArray){
        $deleted = [];
        $couldNotDelete = [];
        foreach ($valuesArray as $key => $values){
            if($this->deleteOne($values) === 0){
                $couldNotDelete[] = $values['id'];
            }else{
                $deleted[] = $values['id'];
            }

        }
        if (!empty($deleted)){
            Feedback::add([$this->view->tr('deleted') =>  $deleted]);
        }
        if (!empty($couldNotDelete)){
            Feedback::add([$this->view->tr('couldnotdelete') => $couldNotDelete]);
        }
        return $deleted;
    }

}
?>
