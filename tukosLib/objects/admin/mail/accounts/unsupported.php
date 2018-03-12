<?php

namespace TukosLib\Objects\Admin\Mail\Accounts;

use TukosLib\Utils\Feedback;

use TukosLib\TukosFramework as Tfk;

class Unsupported  {
   
    function __construct($mailServerFolder){
    }

    function processOne($accountInfo){
        Feedback::add('UnsupportedMailServer');
        return [];
    }
}
?>
