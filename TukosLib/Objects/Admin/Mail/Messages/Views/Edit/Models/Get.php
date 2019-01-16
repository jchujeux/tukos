<?php

namespace TukosLib\Objects\Admin\Mail\Messages\Views\Edit\Models;

use TukosLib\Objects\Views\Edit\Models\Get as GetModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;


class Get extends GetModel {

    protected $newModeAtts = [
        'hidden' => ['date' => true,  'size' => true, 'uid' => true, 'msgno' => true, 'recent' => true, 'flagged' => true, 'answered' => true,
                     'deleted'     => true, 'seen' => true, 'draft' => true, 'udate' => true],
        'disabled' => ['parentid' => false, 'mailboxname' => false, 'name' => false, 'from' => false, 'to' => false, 'body' => false],
    ];
    protected $existingModeAtts = [
        'hidden' => ['mailboxname' => false, 'date' => false,  'size' => false, 'uid' => false, 'msgno' => false, 'recent' => false, 'flagged' => false, 'answered' => false,
                     'deleted'     => false, 'seen' => false, 'draft' => false, 'udate' => false],
        'disabled' => ['parentid' => true, 'mailboxname' => true],
    ]; 

    function get($query, $cols=['*']){
        $data = parent::get($query, $cols);
        return array_merge(
            $data,
            //(empty($where) || (!empty($data['value']['mailboxname']) && $data['value']['mailboxname'] === 'Drafts') ? $this->editModeAtts : $this->viewModeAtts)                    
            (empty($data['value']['id']) ? $this->newModeAtts : $this->existingModeAtts)
        );
    }
    public function updateFormContent(&$formContent, $query, $cols=['*']){
        $formContent['data'] = $this->get($query, $cols);
    }
}
?>
