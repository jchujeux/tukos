<?php 

namespace TukosLib\Controllers;

use TukosLib\Web\FormView;
use TukosLib\Controllers\Dialogue;
use TukosLib\Utils\Translator;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Form extends Translator{

    function __construct(){

        parent::__construct(Tfk::$tr);
        $this->user     = Tfk::$registry->get('user');
        $this->dialogue = Tfk::$registry->get('dialogue');
    }
    function respond($request, $query){
        
        Feedback::reset();
        $formView           = new FormView($this);
        $dialogueController = new Dialogue();

        $request['mode'] = 'Form';
        $request['action'] = 'Tab';
        $isOkForm = $formView->addForm($dialogueController->response($request, $query));

        if ($isOkForm){
            $formView->render();
        }
        return true;
    }
}  
?>
