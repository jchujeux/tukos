<?php 

namespace TukosLib\Controllers;

use TukosLib\Web\PageView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Translator;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class MobilePage extends Translator{

    function __construct(){

        parent::__construct(Tfk::$tr);
        $this->user     = Tfk::$registry->get('user');
        $this->dialogue = Tfk::$registry->get('dialogue');
    }
    function respond($request, $query){
        
        Feedback::reset();
        $pageView           = new PageView($this);
        $dialogueController = new Dialogue();

        $request['mode'] = 'Mobile';
        $request['action'] = 'Tab';
        $isOkTab = (Utl::extractItem('notab', $query) !== 'no') || $pageView->addTab(array_merge($dialogueController->response($request, $query), ['selected' => true]));
        
        if ($isOkTab){
            $pageView->render($this->user->modulesMenuLayout());
        }
        return true;
    }
}  
?>
