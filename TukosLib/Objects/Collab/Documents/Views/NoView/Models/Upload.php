<?php

namespace TukosLib\Objects\Collab\Documents\Views\NoView\Models;

use TukosLib\Objects\Views\Models\AbstractViewModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Upload extends AbstractViewModel{

    function uploadedFilesInfo(){
        $filesInfo = [];
        $properties = ['name', 'type', 'tmp_name', 'error', 'size'];
        $mdate = (isset($_POST['mdate']) ? json_decode($_POST['mdate']) : []);
        if (isset($_FILES['uploadedfiles'])){
            foreach ($_FILES['uploadedfiles']['name'] as $key => $fileName){
                foreach ($properties as $property){
                    $filesInfo[$key][$property] = $_FILES['uploadedfiles'][$property][$key];
                }
                $filesInfo[$key]['mdate'] = (isset($mdate[$key]) ? $mdate[$key] : '');
                $filesInfo[$key] = $this->fromUTC($filesInfo[$key], 'mdate');
            }
        }else if (isset($_FILES['uploadedfile'])){
            $filesInfo[0] = $_FILES['uploadedfile'];
            $filesInfo[0]['mdate'] = (isset($mdate[0]) ? $mdate[0] : '');
            $filesInfo[0] = $this->fromUTC($filesInfo[0], 'mdate');
        }
        return $filesInfo;
    }

    function upload(){
        $filesInfo = $this->uploadedFilesInfo();
        if (isset($_POST['id'])){
            $id = $_POST['id'];
        }else{
            $parentId = json_decode($_POST['parentid'], true);
        }
        if ($filesInfo){
            foreach ($filesInfo as $fileInfo){
                if ($fileInfo['error']){
                    Feedback::add(implode(' ', [$this->model->tr('Thefile'), $fileInfo['name'], $this->model->tr('haserror'), $fileInfo['error'], $this->model->tr('notstored')]));
                }else{
                    unset($fileInfo['error']);
                    $descriptor = $this->model->descriptor($fileInfo['tmp_name']);
                    if (isset($descriptor['id'])){
                        Feedback::add(implode(' ', [$this->model->tr('Thefile'), $fileInfo['name'], $this->model->tr('existswithid'), $descriptor['id'], $this->model->tr('notstored')]));
                    }else{
                        $fileTmpName = Utl::extractItem('tmp_name', $fileInfo);
                        $fileId = $this->model->nextFileId();
                        $this->model->insertFile($fileTmpName, $fileId);
                        $descriptor['fileid'] = $fileId;
                        if (empty($id)){
                            $descriptor['parentid'] = $parentId;
                            $descriptor = $this->model->insertExtended(array_merge($descriptor, $fileInfo), true);
                        }else{
                            $this->model->updateOne(array_merge($descriptor, $fileInfo), ['where' => ['id' => $id]]);
                        }
                        Feedback::add(implode(' ', [$this->model->tr('Thefile'), $fileInfo['name'], $this->model->tr('uploadedwithid'), $descriptor['fileid']]));
                    }
                }
            }
        }else{
            Feedback::add($this->model->tr('noUploadedFiles'));
        }
        return [];
    }

}
?>
