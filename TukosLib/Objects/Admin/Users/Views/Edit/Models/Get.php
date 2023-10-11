<?php

namespace TukosLib\Objects\Admin\Users\Views\Edit\Models;

use TukosLib\Objects\Views\Edit\Models\Get as EditGetModel;
class Get extends EditGetModel {

    protected function setProtection(&$data){
        if (!$this->user->isAtLeastAdmin()/* && !empty($data['value']['id']) && $data['value']['id'] === $this->user->id()*/){
            $disabledCols = ['parentid', 'name', 'rights', 'tukosorganization', 'modules', 'restrictedmodules', 'environment', 'comments', 'permission', 'grade', 'contextid', 'targetdb', 'enableoffline', 'acl', 'delete', 'duplicate', 'new'];
            foreach ($disabledCols as $col){
                $data['disabled'][$col] = true;
            }
            foreach (['modules', 'restrictedmodules', 'acl', 'history'] as $col){
                $data['hidden'][$col] = true;
            }
        }else{
            parent::setProtection($data);
        }
    }
}
?>
