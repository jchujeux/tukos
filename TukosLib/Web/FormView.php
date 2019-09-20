<?php
namespace TukosLib\Web;

use Aura\View\Template;
use Aura\View\EscaperFactory;
use Aura\View\TemplateFinder;
use Aura\View\HelperLocator;
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
        $template = new Template(new EscaperFactory, new TemplateFinder, new HelperLocator);
        $packagesLocation = ['dojo', 'dijit', 'dojox', 'dstore', 'dgrid', 'tukos', 'dojoFixes', 'redips'];
        array_walk($packagesLocation, function(&$module){
            $module = '{"name":"' . $module . '","location":"' . Tfk::moduleLocation($module) . '"}';
        });
        $template->packagesString = '[' . implode(',', $packagesLocation) . ']';
        $template->tukosLocation = Tfk::moduleLocation('tukos');
        $template->dgridLocation = Tfk::moduleLocation('dgrid');
        $template->dojoBaseLocation = Tfk::dojoBaseLocation();
        $template->language = $translatorsStore = Tfk::$registry->get('translatorsStore')->getLanguage();
        $template->loadingMessage = $this->tr('Loading') . '...';
        $formTemplate = $this->isMobile ? "MobileFormTemplate.php" : "FormTemplate.php";
        $this->pageManagerArgs = array_merge($this->pageManagerArgs, ['isMobile' => $this->isMobile, 'extras' => Tfk::getExtras()],
            array_filter(['extendedIds' => SUtl::translatedExtendedIdCols(), 'messages' => Tfk::$registry->get('translatorsStore')->getSetsMessages(['page', 'common']), 'feedback' => Feedback::get()])
        );

        $template->pageManagerArgs = json_encode($this->pageManagerArgs);
        
        $finder = $template->getTemplateFinder();
        $finder->setPaths([dirname(__FILE__)]);
        
        $this->dialogue->response->setContent (Tfk::$registry->get('translatorsStore')->substituteTranslations($template->fetch($formTemplate)));
    }
}
?>
