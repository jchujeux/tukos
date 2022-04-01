<?php
namespace TukosLib\Web;

use Aura\View\ViewFactory;
use TukosLib\Utils\Translator;
use TukosLib\utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;

class FormView extends Translator{

    protected $formContent, $formTitle; 
    
    public function __construct($controller){
        parent::__construct($controller->tr);
        $this->user = $controller->user;
        $this->dialogue = $controller->dialogue;
    	$this->pageManagerArgs = [
            'dialogueUrl' => Tfk::$registry->dialogueUrl,
        ];
    	$this->isMobile = Tfk::$registry->isMobile;

    }
    function addForm($description){
        if ($description === false){
            return false;
        }else{
            $this->pageManagerArgs['formDescription'][] = $description;
            return true;
        }
    }
    function addToPageManager($args){
        $this->pageManagerArgs = Utl::array_merge_recursive_replace($this->pageManagerArgs, $args);
    }

    function render(){
        $view = (new ViewFactory)->newInstance();
        $packagesLocation = ['dojo', 'dijit', 'dojox', 'dstore', 'dgrid', 'tukos', 'dojoFixes', 'redips'];
        array_walk($packagesLocation, function(&$module){
            $module = '{"name":"' . $module . '","location":"' . Tfk::moduleLocation($module) . '"}';
        });
        $view->packagesString = '[' . implode(',', $packagesLocation) . ']';
        $view->tukosLocation = Tfk::moduleLocation('tukos');
        $view->dgridLocation = Tfk::moduleLocation('dgrid');
        $view->dojoBaseLocation = Tfk::dojoBaseLocation();
        $view->language = $translatorsStore = Tfk::$registry->get('translatorsStore')->getLanguage();
        $view->loadingMessage = $this->tr('Loading') . '...';
        $formTemplate = $this->isMobile ? "MobileFormTemplate.php" : "FormTemplate.php";
        $this->pageManagerArgs = array_merge($this->pageManagerArgs, ['isMobile' => $this->isMobile, 'extras' => Tfk::getExtras()],
            array_filter(['extendedIds' => SUtl::translatedExtendedIdCols(), 'messages' => Tfk::$registry->get('translatorsStore')->getSetsMessages(['page', 'common']), 'feedback' => Feedback::get()])
        );

        $view->pageManagerArgs = json_encode($this->pageManagerArgs);
        
        
        $viewRegistry = $view->getViewRegistry();
        $viewRegistry->set('Form', dirname(__FILE__) .  "/$formTemplate");
        
        $view->setView('Form');
        
        $this->dialogue->response->setContent (Tfk::$registry->get('translatorsStore')->substituteTranslations($view()));
    }
}
?>
