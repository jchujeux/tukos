<?php 

namespace TukosLib\Controllers;

use TukosLib\Web\PageView;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Controllers\Dialogue;
use TukosLib\Utils\Translator;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Page extends Translator{


    function __construct(){

        parent::__construct(Tfk::$tr);
        $this->user     = Tfk::$registry->get('user');
        $this->dialogue = Tfk::$registry->get('dialogue');
    }
    
    function respond($request, $query){
        
        Feedback::reset();
        $pageView           = new PageView($this);
        $dialogueController = new Dialogue();

        $isOkAccordions = true;
        $appConfig= Tfk::$registry->get('appConfig');
        $pageCustom = $this->user->pageCustomization();
        $panesCustomization = isset($pageCustom['panesConfig']) ? Utl::toAssociative($pageCustom['panesConfig'], 'name') : [];
        foreach ($appConfig->accordions as $name => $accordionRequest){
        	if ($this->user->isAllowed($request['object'], [])){
	        	if ($accordionRequest['view'] === 'edit'){
	            	$accordionDescription = ['formContent' => ['object' => $accordionRequest['object'], 'viewMode' => 'edit', 'action' => 'tab', 'query' => $accordionRequest['query']]];
	            }else{
	            	$accordionDescription = $dialogueController->response($accordionRequest, [], true);
	            }
	            if (!empty($accordionRequest['title'])){
	            	$accordionDescription['title'] = $this->tr($accordionRequest['title']);
	            }
	            $id = $accordionDescription['id'] = $accordionRequest['id'];
	            if (isset($panesCustomization[$id]) && isset($panesCustomization[$id]['selected']) && $panesCustomization[$id]['selected'] === 'on'){
	            	$accordionDescription['selected'] = true;
	            }
	            $pageView->addAccordion($accordionDescription);
        	}
        }

        $request['action'] = 'tab';
        $isOkTab = $pageView->addTab($dialogueController->response($request, $query));
        $pageView->setFocusedTab(0);

        if ($isOkAccordions && $isOkTab){
            Feedback::add($this->tr('svrexectime') . (microtime(true) - Tfk::$startMicroTime));
            $pageView->render($this->user->modulesMenuLayout());
        }
        return true;
    }
}  
?>
