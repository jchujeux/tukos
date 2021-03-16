<?php
namespace TukosLib\Web;

use Aura\View\Template;
use Aura\View\EscaperFactory;
use Aura\View\TemplateFinder;
use Aura\View\HelperLocator;
use TukosLib\Utils\Translator;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\TukosFramework as Tfk;

class BlogView extends Translator{

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
    function addTab($description){
        if ($description === false){
            return false;
        }else{
            $this->pageManagerArgs['tabsDescription'][] = $description;
            return true;
        }
    }
    function addRightPane($description){
        if (empty($description)){
            return false;
        }else{
            $this->pageManagerArgs['rightPaneDescription'] = $description;
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
        $template->language = Tfk::$registry->get('translatorsStore')->getLanguage();
        $template->loadingMessage = $this->tr('Loading') . '...';
        $blogTemplate = $this->isMobile ? "MobileBlogTemplate.php" : "BlogTemplate.php";
        if ($this->isMobile){
            $blogTemplate = "MobileBlogTemplate.php";
        }else{
            $blogTemplate = "BlogTemplate.php";
            $logoImageTag = '';// '<img alt="logo" src="' . Tfk::publicDir . 'images/tukosswissknife.png" style="height: ' . ($this->isMobile ? '40' : '100') . 'px; width: ' . ($this->isMobile ? '60' : '150') . 'px;' . ($this->isMobile ? 'float: right;' : '') . '">';
            $this->pageManagerArgs['headerContent'] = <<<EOT
<table width="100%"><tr><td style="text-align:left;">{$logoImageTag}<span id="tukosHeaderLoading"></span></td><td style="text-align:center;"><H1>{$this->tr('tukosBlogTitle')}</H1></td><td style="text-align:right;"><b><i>The Ultimate Knowledge Organizational System</i></b></td></table>
EOT
            ;
            $blogModel = Tfk::$registry->get('objectsStore')->objectModel('blog');
            $onClickString = $blogModel->onClickGotoTabString('edit', "name:'{$this->tr('BlogWelcome')}'");
            $this->pageManagerArgs['rightPaneContent'] = '<div style="background-color: #d0e9fc;text-align: center;" ' . $onClickString . '></br><img alt="logo" src="' . Tfk::publicDir . 'images/tukosswissknife.png" style="height:150px; width: 200px;"></br>' . 
                '<span style="' . HUtl::urlStyle() . "\">{$this->tr('BlogWelcome')}</span></div>";
        }
        $template->pageManagerArgs = json_encode($this->pageManagerArgs);
        
        $finder = $template->getTemplateFinder();
        $finder->setPaths([dirname(__FILE__)]);
        
        $this->dialogue->response->setContent (Tfk::$registry->get('translatorsStore')->substituteTranslations($template->fetch($blogTemplate)));
    }
}
?>
