<?php 

namespace TukosLib\Controllers;

use TukosLib\Web\BlogView;
use TukosLib\Utils\Translator;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Blog extends Translator{

    function __construct(){

        parent::__construct(Tfk::$tr);
        $this->user     = Tfk::$registry->get('user');
        $this->dialogue = Tfk::$registry->get('dialogue');
    }
    function respond($request, &$query){
        
        Feedback::reset();
        $blogView           = new BlogView($this);
        $dialogueController = new Dialogue();

        $request['mode'] = 'Blog';
        $request['action'] = 'Tab';
        $query = array_merge(['object' => 'blog', 'form' => 'Show'], $query);
        Tfk::$registry->blogRightPanelWidth = Utl::extractItem('rightpanelwidth', $query, '15%');
        $blogView->addRightPane($dialogueController->response(['object' => 'blog', 'view' => 'Pane', 'action' => 'Accordion', 'mode' => 'accordion',  'pane' => 'rightPane'],  [], true));
        $isOkBlog = $blogView->addTab(array_merge($dialogueController->response($request, $query), ['selected' => true]));

        if ($isOkBlog){
            $blogView->render();
        }
        return true;
    }
}  
?>
