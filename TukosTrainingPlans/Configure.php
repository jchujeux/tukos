<?php 
namespace TukosTrainingPlans; 

use TukosLib\AbstractConfigure;

class Configure extends AbstractConfigure{
    
    function __construct(){
               
        parent::__construct(
            ['#sptathletes' => [], '#sptplans' => [['#sptworkouts' => []]], '#sptequipments' => [], '#stravaactivities' => []],  
            ['users', 'people', 'organizations', 'sptathletes', 'sptplans', 'sptworkouts'],
            []
        );
        
    }
}
AbstractConfigure::__initialize();
?>
