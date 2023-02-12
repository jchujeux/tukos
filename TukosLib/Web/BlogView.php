<?php
namespace TukosLib\Web;

use Aura\View\ViewFactory;
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
        $view = (new ViewFactory)->newInstance();
        if (Tfk::$registry->isCrawler){
            $blogTemplate = "CrawlerBlogTemplate.php";
            $view->title = $this->pageManagerArgs['tabsDescription'][0]['formContent']['data']['value']['name'];
            $view->content = $this->pageManagerArgs['tabsDescription'][0]['formContent']['data']['value']['comments'];
        }else{
            $packagesLocation = ['dojo', 'dijit', 'dojox', 'dstore', 'dgrid', 'tukos', 'dojoFixes', 'redips'];
            array_walk($packagesLocation, function(&$module){
                $module = '{"name":"' . $module . '","location":"' . Tfk::moduleLocation($module) . '"}';
            });
                $view->packagesString = '[' . implode(',', $packagesLocation) . ']';
                $view->tukosLocation = Tfk::moduleLocation('tukos');
                $view->dgridLocation = Tfk::moduleLocation('dgrid');
                $view->dojoBaseLocation = Tfk::dojoBaseLocation();
                $view->language = Tfk::$registry->get('translatorsStore')->getLanguage();
                $view->loadingMessage = $this->tr('Loading') . '...';
                $blogTitle = $this->tr(Tfk::$registry->blogTitle);
                if ($this->pageManagerArgs['isMobile'] = Tfk::$registry->isMobile){
                    $blogTemplate = "MobileBlogTemplate.php";
                    $this->pageManagerArgs['headerTitle'] = $blogTitle;
                }else{
                    $blogTemplate = "BlogTemplate.php";
                    $this->pageManagerArgs['headerContent'] = <<<EOT
<table width="100%"><tr><td style="text-align:left;"><span id="tukosHeaderLoading"></span></td><td style="text-align:center;"><H1>{$blogTitle}</H1></td><td style="text-align:right;"><b><i>The Ultimate Knowledge Organizational System</i></b></td></table>
EOT
                    ;
                    $blogModel = Tfk::$registry->get('objectsStore')->objectModel('blog');
                    $onClickString = $blogModel->onClickGotoTabString('edit', "name:'{$this->tr('BlogWelcome')}'");
                    $this->pageManagerArgs['rightPaneContent'] = '<div style="background-color: #d0e9fc;text-align: center;" ' . $onClickString . '></br><img alt="logo" src="' . Tfk::$publicDir . 'images/tukosswissknife.png" style="height:150px; width: 200px;"></br>' .
                        '<span style="' . HUtl::urlStyle() . "\">{$this->tr('BlogWelcome')}</span></div>";
                }
        }
        $view->pageManagerArgs = json_encode($this->pageManagerArgs);
        
        $viewRegistry = $view->getViewRegistry();
        $viewRegistry->set('Blog', dirname(__FILE__) .  "/$blogTemplate");
        
        $view->setView('Blog');
        
        $this->dialogue->response->setContent (Tfk::$registry->get('translatorsStore')->substituteTranslations($view()));
    }
}
?>
