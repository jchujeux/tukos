<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\TukosFramework as Tfk;

class ViewCustomMore extends AbstractAction{
    function response($query){
        $response['view'] = $this->view->user->getCustomView($this->view->objectName, 'edit');

        if (!empty($query['id'])){
            $response['item'] = $this->view->model->getItemCustomization(['id' => $query['id']], ['edit'], true);
        }
        return $response;
    }
}
?>
