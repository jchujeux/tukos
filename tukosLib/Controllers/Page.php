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

        $isOkAccordion = true;
        $appConfig= Tfk::$registry->get('appConfig');
        $pageCustom = $this->user->pageCustomization();
        $panesCustomization = isset($pageCustom['panesConfig']) ? Utl::toAssociative($pageCustom['panesConfig'], 'name') : [];
        foreach ($appConfig->accordion as $configRequest){
        	if ($this->user->isAllowed($configRequest['object'], [])){
	        	if (($view = $configRequest['view']) === 'edit' || $view === 'overview'){
	            	$description = ['formContent' => ['object' => $configRequest['object'], 'viewMode' => $view, 'paneMode' => 'accordion', 'action' => 'tab']];
	            }else{
	            	$configRequest['mode'] = 'accordion';
	            	$description = $dialogueController->response($configRequest, [], true);
	            }
	            if (!empty($configRequest['title'])){
	            	$description['title'] = $this->tr($configRequest['title']);
	            }
	            $id = $description['id'] = 'pane_' . $configRequest['pane'];
	            if (isset($panesCustomization[$id]) && isset($panesCustomization[$id]['selected']) && $panesCustomization[$id]['selected'] === 'on'){
	            	$description['selected'] = true;
	            }
	            if (!empty($configRequest['config'])){
	            	$description['config'] = $configRequest['config'];
	            }
	            $pageView->addAccordionPane($description);
        	}
        }

        $request['mode'] = $request['action'] = 'tab';
        $isOkTab = $pageView->addTab($dialogueController->response($request, $query));
        $pageView->setFocusedTab(0);

        if ($isOkAccordion && $isOkTab){
            Feedback::add($this->tr('svrexectime') . (microtime(true) - Tfk::$startMicroTime));
            $pageView->render($this->user->modulesMenuLayout());
        }
        return true;
    }
}  
?>
