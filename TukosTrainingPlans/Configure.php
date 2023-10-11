<?php 
namespace TukosTrainingPlans; 

use TukosLib\AbstractConfigure;

class Configure extends AbstractConfigure{
    
    function __construct(){
               
        parent::__construct([],  ['users', 'people', 'organizations', 'sptathletes', 'sptprograms', 'sptsessions'], []);
        
    }
}
AbstractConfigure::__initialize();
?>
