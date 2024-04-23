<?php 
namespace TukosTrainingPlans; 

use TukosLib\AbstractConfigure;

class Configure extends AbstractConfigure{
    
    function __construct(){
               
        parent::__construct([],  ['users', 'people', 'organizations', 'sptathletes', 'sptplans', 'sptworkouts', 'sptprograms', 'sptsessions'], []);
        
    }
}
AbstractConfigure::__initialize();
?>
