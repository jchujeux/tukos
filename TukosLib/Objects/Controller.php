<?php
namespace TukosLib\Objects;

//use TukosLib\Utils\Response;
use TukosLib\Utils\TukosException;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Controller extends ObjectTranslator{

    function __construct($objectName){
        parent::__construct($objectName);
        $this->objectsStore     = Tfk::$registry->get('objectsStore');
        $this->objectName = $objectName;
        $this->user  = Tfk::$registry->get('user');
        $this->model = $this->objectsStore->objectModel($objectName, $this->tr);
        $this->view  = $this->objectsStore->objectView($objectName, $this->tr);
        $this->dialogue  = Tfk::$registry->get('dialogue');
    }
    protected function exceptionFeedback($request, $title, $eMessage){
        if ($request['action'] === 'Tab'){
            return    ['title'   => $title, 
                          'content' => '<center><h2>Exception raized during action <i>' . $request['view'] . '</i></h2>' . $eMessage];
        }else{
            Feedback::add([$title => $eMessage]);
            return ['feedback' => Feedback::get()];
        }
    }

    function response($request, $query, $ignoreUnallowed = false){
        $this->request = $request;
        $this->paneMode = $request['mode'];
        $this->query = $query;
        if ($this->user->isAllowed($request['object'], $query)){
            try{
                $action = $this->objectsStore->objectAction($this, $request);
                //$this->view->request = $request;
                return $action->response($query);
            }
            catch (TukosException $e){
                return $this->exceptionFeedback($request, 'tukos 2.0 - Exception (developer error)', $e->getMessage());
            }
            catch(\Exception $e){
                return $this->exceptionFeedback($request, 'tukos 2.0 - Exception (unknown error)', $e->getMessage());
            }            
        }else{
            if ($ignoreUnallowed){
                return [];
            }else{
                return  ['title'   => $this->tr('wrongmodule'), 
                            'content' =>'<center><h2>' . $this->tr('Accessdeniedtomodule') . ' <i>' . $this->tr($request['object']) . '</i>, ' . $this->tr('ormoduledoesnotexists') . '</h2>',
                            'focusOnOpen' => true,  'closable'    => true,
                ];
            }
        }
    }
}
?>
