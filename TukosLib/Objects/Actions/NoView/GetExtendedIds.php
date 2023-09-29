<?php

namespace TukosLib\Objects\Actions\NoView;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\Objects\StoreUtilities as SUtl;

class GetExtendedIds extends AbstractAction{
    function response($query){
        SUtl::addIdCols($query['storeatts']['where']['ids']);
        return [];
    }
}
?>
