<?php

namespace TukosLib\Objects\Collab\Documents\Views\NoView\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Utils\HttpUtilities;
use TukosLib\Utils\Feedback;

class Download extends AbstractViewModel{

    function download($query){
        if (!empty($query['id'])){
            $fileInfo = $this->model->getOne(['where' => ['id' => $query['id']], 'cols' => ['name', 'mdate', 'type', 'size', 'fileid']]);
            HttpUtilities::setHeaderAndCookie($fileInfo, $query['downloadtoken']);
            $this->model->echoFile($fileInfo['fileid']);
            return false;
        }else{
            Feedback::add($this->view->tr('EmptyId'));
            return [];
        }
    }

}
?>
