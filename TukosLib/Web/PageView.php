<?php
namespace TukosLib\Web;

use Aura\View\Template;
use Aura\View\EscaperFactory;
use Aura\View\TemplateFinder;
use Aura\View\HelperLocator;
use TukosLib\Web\PageCustomization;
use TukosLib\Utils\Translator;
use TukosLib\utils\Feedback;
use TukosLib\Utils\Widgets;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Objects\Directory;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class PageView extends Translator{

    use PageCustomization;
    
    protected $tabContent, $tabTitle; 
    
    public function __construct($controller){
        parent::__construct($controller->tr);
        $this->user = $controller->user;
        $this->dialogue = $controller->dialogue;
    	$this->leftPaneButtons = '<button data-dojo-type="dijit/form/Button" data-dojo-props="showLabel: false" type="button" id="showHideLeftPane">' . $this->tr('showhideleftpane') . '</button>'.
    							 '<button data-dojo-type="dijit/form/Button" data-dojo-props="showLabel: false" type="button" id="showMaxLeftPane">' . $this->tr('maximinimizeleftpane') . '</button>';
    	$this->pageManagerArgs = [
            'contextTreeAtts' => array_merge($this->user->contextTreeAtts($this->tr), ['style' => ['width' => '15em', 'backgroundColor' => '#F8F8F8']]),
            'sortParam' => 'sort',
            'dialogueUrl' => Tfk::$registry->dialogueUrl,
    	    'pageUrl' => Tfk::$registry->pageUrl,
            'accordionDescription' => [],
            'tabsDescription' => [],
    		'navigationTree' => 'navigationTree',
    	    'userid' => $this->user->id()
        ];
    	$this->accordionStoreData = [];
    }
    function addToPageManagerArgs($attribute, $value){
        $this->pageManagerArgs[$attribute] = $value;
    }
    function addAccordionPane($description){
        if (empty($description)){
            return false;
        }else{
            $this->pageManagerArgs['accordionDescription'][] = $description;
            $this->accordionStoreData[] = ['id' => $description['id'], 'name' => $description['title']];
            return true;
        }
    }

    function addTab($description){
        if ($description === false){
            return false;
        }else{
            $this->pageManagerArgs['tabsDescription'][] = $description;
            return true;
        }
    }

    private function onTriggerUrlArgs($object, $view){
        return ['object' => $object, 'view' => $view, 'mode' => 'Tab', 'action' => 'Tab'];
    }

    private function defaultModuleActions($object, $mode, $customAtts){
        $editOverview = [
            'edit' => [
                'type' => 'PopupMenuItem',
                'atts' => ['label' => $this->tr('edit')],
                'popup' => Widgets::objectSelect(['placeHolder' => Tfk::tr('selectanitem'), 'onChangeArgs' => $this->onTriggerUrlArgs($object, 'Edit'), 'object' => $object, 'mode' => 'Tab'], true),
            ],
            'overview' => ['type' => 'MenuItem',     'atts' => ['onClickArgs' => $this->onTriggerUrlArgs($object, 'Overview'), 'label' => $this->tr('overview')]],
        ];
        return Utl::array_merge_recursive_replace($mode === '$' ? $editOverview : array_merge([
            'new' => [
                'type' => 'PopupMenuItem',    
                 'atts' => ['label' => $this->tr('new')],
                 'popup' => [
                    'type'  => 'DropDownMenu',
                    'items' => [
                        ['type' => 'MenuItem', 'atts' => ['onClickArgs' => $this->onTriggerUrlArgs($object, 'Edit'),     'label' => $this->tr('default')]],
                        ['type' => 'PopupMenuItem',   'atts' => ['label' => $this->tr('fromtemplate')], 
                         'popup' => Widgets::objectSelect(['placeHolder' => Tfk::tr('selectatemplate'), 'onChangeArgs' => $this->onTriggerUrlArgs($object, 'Edit'), 'sendAsNew' => true, 'object' => $object, 'mode' => 'Tab', 'dropdownFilters' => ['grade' => 'TEMPLATE']], true),
                        ]
                    ],
                ],
            ]],
            $editOverview
        ), $customAtts);

    }

    protected function buildDescription($key, $layout, &$theDescription){
        if (in_array($key[0], ['#', '$', '@'])){
            $module = substr($key, 1);
            if (!in_array($module, $this->user->allowedModules())){
                return;
            }
        }else{
            $module = $key;
        }
        $customAtts = Utl::extractItem('customAtts', $layout, []);
        if (empty($layout['atts']) || !isset($layout['atts']['label'])){
            $layout['atts']['label'] = $this->tr($module);
            $layout['atts']['moduleName'] = $module;
        }
        $theDescription[$module]['atts'] = $layout['atts'];
        $contexts = ['tukosContext' => $this->user->customContextId($module, 'tukos'), 'userContext' => $this->user->customContextId($module, 'user'), 'activeContext' => $this->user->getContextId($module)];
        foreach($contexts as $name => $contextId){
            if ($contextId){
                SUtl::addIdCol($contextId);
                $theDescription[$module]['atts'][$name] = $contextId;
            }
        }
        $theDescription[$module]['type'] = $layout['type'];
        if (isset($layout['popup'])){
        	$type = $layout['popup']['type'];
        	$theDescription[$module]['popup'] = strtoupper($type[0]) !== $type[0] ? Widgets::$type($layout['popup']['atts'], true) : $layout['popup'];
        }
        if (in_array($key[0], ['#', '$'])){
            $theDescription[$module]['popup'] = ['type' => 'DropDownMenu', 'items' => $this->defaultModuleActions($module, $key[0], $customAtts)];
        	$theDescriptionItems = &$theDescription[$module]['popup'];
        }else if(isset($layout['type']) && in_array($layout['type'], ['PopupMenuBarItem', 'PopupMenuItem']) && !isset($theDescription[$module]['popup'])){
        	$theDescription[$module]['popup'] = ['type' => 'DropDownMenu'];
        	$theDescriptionItems = &$theDescription[$module]['popup'];
        }else{
        	if (isset($layout['type']) && in_array($layout['type'], ['MenuItem', 'MenuBarItem'])){
        	    if (is_array($queryId = Utl::drillDown($layout, ['atts', 'onClickArgs', 'query', 'id']))){
        	        $theItem = Tfk::$registry->get('objectsStore')->objectModel($queryId['object'])->getOne(['where' => ['id' => $queryId['id']], 'cols' => [$queryId['col']]]);
        	        $theDescription[$module]['atts']['onClickArgs']['query']['id'] = empty($theItem) ? '' : $theItem[$queryId['col']];
        	    }
        	}
            $theDescriptionItems = &$theDescription[$module];
        }
        if (!empty($layout[0])){
            if (empty($theDescriptionItems['items'])){
                $theDescriptionItems['items'] = [];
            }
            foreach ($layout[0] as $itemKey => $itemLayout){
                if (empty($itemLayout['type'])){
                	$itemLayout['type'] = 'PopupMenuItem';
                }
            	$this->buildDescription($itemKey, $itemLayout, $theDescriptionItems['items']);
            }
            if (empty($theDescriptionItems['items'])){
                //unset($theDescriptionItems);
                unset($theDescription[$module]);
            }
        }
    }
    public function menuBarDescription($modulesMenuLayout){
        $theDescription = [];
    foreach ($modulesMenuLayout as $key => $layout){
            if (empty($layout['type'])){
                $layout['type'] = 'PopupMenuBarItem';
            }
            $this->buildDescription($key, $layout, $theDescription);
        }
        return ['items' => $theDescription];
    }
    function welcomeConnect($logoutUrl){
        return $this->tr('Welcome') . ', <span id="pageusername" style="text-decoration:underline; color:blue; cursor:pointer">' .
               $this->user->username() . '</span> <a href="' . $logoutUrl . '" />' . $this->tr('logout') . '</a>';
    }
    function addToPageManager($args){
        $this->pageManagerArgs = Utl::array_merge_recursive_replace($this->pageManagerArgs, $args);
    }
    function render($modulesMenuLayout){
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
        $this->pageManagerArgs['menuBarDescription'] = $this->menuBarDescription($modulesMenuLayout);
        $this->pageManagerArgs['objectsDomainAliases'] = Directory::objectsDomainAliases();
        $this->pageManagerArgs['userRights'] = $this->user->rights();
        
        if ($this->pageManagerArgs['isMobile'] = Tfk::$registry->isMobile){
            $this->pageManagerArgs['headerContent'] = $this->tr(Tfk::$registry->appName . 'HeaderBanner');
            $pageTemplate = "MobilePageTemplate.php";
        }else{
            $this->pageManagerArgs['headerContent'] = Utl::substitute(
                '<table width="100%"><tr><td> ${buttons}<b>Tukos 2.0</b><span id="tukosHeaderLoading"></span></td><td align="center"><b><i>${header} ${ownerorg}</i></b></td><td align="right">${welcome}</td></table>', [
                    'buttons' => empty($this->pageManagerArgs['accordionDescription']) ? '' : $this->leftPaneButtons, 'header' => $this->tr(Tfk::$registry->appName . 'HeaderBanner', 'none'),
                    'ownerorg' => $this->tr($this->user->tukosOrganization(), 'none'), 'welcome' => $this->welcomeConnect(Tfk::$registry->pageUrl . 'auth/logout')]
            );
            $this->pageManagerArgs['userEditUrl'] = ['object' => 'users', 'view' => 'Edit', 'mode' => 'Tab', 'action' => 'Tab', 'query' => ['id' => $this->user->id()]];
            foreach (['combined' => 'pageCustomization', 'user' => 'pageUserCustomization', 'tukos' => 'pageTukosCustomization'] as $mode => $pageModeCustomization){
                $this->pageManagerArgs[$pageModeCustomization] = $this->user->PageCustomization($mode);
                if (isset($this->pageManagerArgs[$pageModeCustomization]['panesConfig'])){
                    SUtl::addItemsIdCols($this->pageManagerArgs[$pageModeCustomization]['panesConfig'], ['id']);
                }
            }
            $this->pageManagerArgs['pageCustomDialogDescription'] = $this->pageCustomDialogDescription($this->pageManagerArgs['pageCustomization']);
            $pageTemplate = 'PageTemplate.php';
        }
        $this->pageManagerArgs = array_merge($this->pageManagerArgs, ['extras' => Tfk::getExtras()],
            array_filter(['extendedIds' => SUtl::translatedExtendedIdCols(), 'messages' => Tfk::$registry->get('translatorsStore')->getSetsMessages(['page', 'common']), 'feedback' => Feedback::get()])
        );
        $template->pageManagerArgs = json_encode($this->pageManagerArgs);
        $finder = $template->getTemplateFinder();
        $finder->setPaths([dirname(__FILE__)]);
        $this->dialogue->response->setContent (Tfk::$registry->get('translatorsStore')->substituteTranslations($template->fetch($pageTemplate)));
    }
}
?>
