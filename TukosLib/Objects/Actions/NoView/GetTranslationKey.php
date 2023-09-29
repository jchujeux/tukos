<?php

namespace TukosLib\Objects\Actions\NoView;

use TukosLib\Objects\Actions\AbstractAction;

class GetTranslationKey extends AbstractAction{
    function response($query){
        return ['key' => $this->user->googleTranslationAccessKey()];
    }
}
?>
