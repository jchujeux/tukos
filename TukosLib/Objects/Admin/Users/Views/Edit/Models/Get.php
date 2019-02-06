<?php

namespace TukosLib\Objects\Admin\Users\Views\Edit\Models;

use TukosLib\Objects\Views\Edit\Models\Get as EditGetModel;
/*
use TukosLib\Objects\Views\Edit\Models\SubObjectsGet;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;
*/
class Get extends EditGetModel {

    protected function setProtection(&$data){
        if ($this->user->rights() !== 'SUPERADMIN' && !empty($data['value']['id']) && $data['value']['id'] === $this->user->id()){
            $disabledCols = ['parentid', 'name', 'rights', 'modules', 'environment', 'comments', 'permission', 'grade', 'contextid', 'targetdb'];
            foreach ($disabledCols as $col){
                $data['disabled'][$col] = true;
            }
        }else{
            parent::setProtection($data);
        }
    }
}
?>
