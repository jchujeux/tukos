<?php 
namespace TukosBlog; 

use TukosLib\AbstractConfigure;
use TukosLib\TukosFramework as Tfk;

class Configure extends AbstractConfigure{

    function __construct(){

        $this->userName = 'tukosBackOffice';
        Tfk::$registry->blogUrl = Tfk::$registry->rootUrl . '/blog';
        Tfk::$registry->blogTitle = 'tukosblogtitle';

        parent::__construct([], [], [], false, 'tukosblog');
    }
}
AbstractConfigure::__initialize();
?>
