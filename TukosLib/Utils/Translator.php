<?php
namespace TukosLib\Utils;

abstract class translator {
    function __construct($translator=null){
        $this->tr = ($translator ? $translator : function($theText){return is_string($theText) ? $theText : implode('', $theText);});
    }
    function tr($theText, $mode=null){
        return call_user_func($this->tr, $theText, $mode);
    }
}
?>
