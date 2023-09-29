<?php 
namespace TukosWoundTrack; 

use TukosLib\AbstractConfigure;

class Configure extends AbstractConfigure{
    
    function __construct(){
        
        parent::__construct('tukossportstds', [],  ['users', 'people', 'organizations', 'physiopatients', 'physiogametracks'], []);
        
    }
}
AbstractConfigure::__initialize();
?>
