<?php

namespace TukosLib\Objects\Collab\Documents\Views\NoView\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Download extends AbstractViewModel{

    function download($query){
        if (!empty($query['id'])){
            $fileInfo = $this->model->getOne(['where' => ['id' => $query['id']], 'cols' => ['name', 'mdate', 'type', 'size', 'fileid']]);
            header("Content-type:" . $fileInfo['type']);
            header("Last-Modified:" . gmdate("D, d M Y H:i:s", strtotime($fileInfo['mdate'])) . " GMT");
            header("Content-length:" . $fileInfo['size']);
            header("Content-Disposition: attachment; filename=" . $fileInfo['name']);
            header("Content-Description: PHP Generated Data");

            setcookie('downloadtoken', $query['downloadtoken'], 0, '/');
            
            $this->model->echoFile($fileInfo['fileid']);

            return false;
        }else{
            Feedback::add($this->view->tr('EmptyId'));
            return [];
        }
    }

}
?>
