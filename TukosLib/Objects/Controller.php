<?php
namespace TukosLib\Objects;

//use TukosLib\Utils\Response;
use TukosLib\Utils\TukosException;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
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
                          'content' => '<center><h2>' . $this->tr('Exceptionduringaction') . ' <i>' . $request['view'] . '</i></h2>' . $eMessage];
        }else{
            Feedback::add([$title => $eMessage]);
            return ['feedback' => Feedback::get()];
        }
    }

    function response($request, $query, $ignoreUnallowed = false){
        $this->request = $request;
        $this->paneMode = $request['mode'];
        if ($request['action'] === 'ObjectSelect' || $this->user->isAllowed($request['object'], $query)){
            try{
                $action = $this->objectsStore->objectAction($this, $request);
                if (isset($query['storeatts']) && isset($this->view->dataWidgets) && !empty($query['storeatts']['where'])){
                    $where = &$query['storeatts']['where']; $storesData = [];
                    $where = array_merge($this->user->getCustomView($this->objectName, 'overview', $this->paneMode, ['data', 'filters', 'overview']), $where);
                    foreach($where as $widgetName => $condition){
                        if (!empty($description = Utl::getItem($widgetName, $this->view->dataWidgets)) && $description['type'] === 'storeSelect'){
                            $storesData[$widgetName] = $description['atts']['edit']['storeArgs']['data'];
                        }
                    }
                    $storesData = json_decode(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode($storesData)), true);
                    foreach($storesData as $widgetName => $storeData){
                        list($opr, $value) = $where[$widgetName];$cache = [];
                        if ($value){
                            $where[$widgetName][1] = ($opr === 'RLIKE' || $opr === 'NOT RLIKE') ? Utl::includesReplace($storeData, 'name', $value, 'id', $cache, true, true) : Utl::findReplace($storeData, 'name', $value, 'id', $cache, true, true);
                        }
                    }
                }
                $this->query = $query;
                
                return $action->response($query);
            }
            catch (TukosException $e){
                return $this->exceptionFeedback($request, 'tukos 2.0 - Exception (developer error)', $e->getMessage());
            }
            catch(\Exception $e){
                return $this->exceptionFeedback($request, $this->tr('TukosUserError'), $e->getMessage());
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
