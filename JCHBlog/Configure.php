<?php 
namespace JCHBlog; 

use TukosLib\AbstractConfigure;
use TukosLib\TukosFramework as Tfk;

class Configure extends AbstractConfigure{
    
    function __construct(){
        
        $this->userName = 'tukosBackOffice';
        Tfk::$registry->blogUrl = Tfk::$registry->rootUrl . '/jch/blog';
        Tfk::$registry->blogTitle = 'jchblogtitle';
        
        parent::__construct([], [], [], false, 'jchblog');
        
    }
}
AbstractConfigure::__initialize();
?>
