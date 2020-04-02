<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\XlsxInterface;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\TukosFramework as Tfk;

trait SessionsFeedbackUtils{

    public function instantiateVersion($version){
        $versionClass = 'TukosLib\\Objects\\Sports\\Programs\\SessionsFeedback' . $version;
        $this->version = new $versionClass();
    }
}
?>
