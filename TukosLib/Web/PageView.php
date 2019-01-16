<?php
namespace TukosLib\Web;

use Aura\View\Template;
use Aura\View\EscaperFactory;
use Aura\View\TemplateFinder;
use Aura\View\HelperLocator;

//use TukosLib\Web\Dialogue;

use TukosLib\Utils\Translator;
use TukosLib\utils\Feedback;
use TukosLib\Utils\Widgets;
use TukosLib\Objects\StoreUtilities as SUtl;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class PageView extends Translator{

    protected $tabContent, $tabTitle; 
    
    public function __construct($controller){
        parent::__construct($controller->tr);
        $this->user = $controller->user;
        $this->dialogue = $controller->dialogue;
    	$this->leftPaneButtons = '<button data-dojo-type="dijit/form/Button" data-dojo-props="showLabel: false" type="button" id="showHideLeftPane">' . $this->tr('showhideleftpane') . '</button>'.
    							 '<button data-dojo-type="dijit/form/Button" data-dojo-props="showLabel: false" type="button" id="showMaxLeftPane">' . $this->tr('showhideleftpane') . '</button>';
    	$this->pageManagerArgs = [

            'contextTreeAtts' => array_merge($this->user->contextTreeAtts($this->tr), ['style' => ['width' => '15em', 'backgroundColor' => '#F8F8F8']]),
            'sortParam' => 'sort',
            'dialogueUrl' => Tfk::$registry->dialogueUrl,
            'accordionDescription' => [],
            'tabsDescription' => [],
    		'navigationTree' => 'navigationTree'
        ];
    	$this->accordionStoreData = [];

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
    
    function setFocusedTab($tabIndex){
        $this->pageManagerArgs['focusedTab'] = $tabIndex;
    }

    private function onTriggerUrlArgs($object, $view){
        return ['object' => $object, 'view' => $view, 'mode' => 'Tab', 'action' => 'Tab'];
    }

    private function defaultModuleActions($object){
        return  [
            'new' => [
                'type' => 'PopupMenuItem',    
                 'atts' => ['label' => $this->tr('new')],
                 'popup' => [
                    'type'  => 'DropDownMenu',
                    'items' => [
                        ['type' => 'MenuItem', 'atts' => ['onClickArgs' => $this->onTriggerUrlArgs($object, 'edit'),     'label' => $this->tr('default')]],
                        ['type' => 'PopupMenuItem',   'atts' => ['label' => $this->tr('fromtemplate')], 
                         'popup' => Widgets::objectSelect(['placeHolder' => Tfk::tr('selectatemplate'), 'onChangeArgs' => $this->onTriggerUrlArgs($object, 'edit'), 'sendAsNew' => true, 'object' => $object, 'mode' => 'Tab', 'dropdownFilters' => ['grade' => 'TEMPLATE']], true),
                        ]
                    ],
                ],
            ],

            'edit' => [
                'type' => 'PopupMenuItem', 
                'atts' => ['label' => $this->tr('edit')],
                'popup' => Widgets::objectSelect(['placeHolder' => Tfk::tr('selectanitem'), 'onChangeArgs' => $this->onTriggerUrlArgs($object, 'edit'), 'object' => $object, 'mode' => 'Tab'], true),
            ],
            'overview' => ['type' => 'MenuItem',     'atts' => ['onClickArgs' => $this->onTriggerUrlArgs($object, 'overview'), 'label' => $this->tr('overview')]],
            //'massedit' => ['type' => 'MenuItem',      'atts' => ['onTriggerUrlArgs' => $this->onTriggerUrlArgs($object, 'massedit'), 'label' => $this->tr('massedit')]],
        ];
    }

    protected function buildDescription($key, $layout, &$theDescription){
        if (in_array($key[0], ['#', '@'])){
            $module = substr($key, 1);
            if (!in_array($module, $this->user->allowedModules())){
                return;
            }
        }else{
            $module = $key;
        }
        if (empty($layout['atts']) || !isset($layout['atts']['label'])){
            $layout['atts']['label'] = $this->tr($module);
            $layout['atts']['moduleName'] = $module;
        }
        $theDescription[$module]['atts'] = $layout['atts'];
        $context = $this->user->customContextId($module);
        if ($context){
            SUtl::addIdCol($context);
        	$theDescription[$module]['atts']['context'] = $context;
        }
        $theDescription[$module]['type'] = $layout['type'];
        if (isset($layout['popup'])){
        	$type = $layout['popup']['type'];
        	$theDescription[$module]['popup'] = Widgets::$type($layout['popup']['atts'], true);
        }
        if ($key[0] === '#'){
            $theDescription[$module]['popup'] = ['type' => 'DropDownMenu', 'items' => $this->defaultModuleActions($module)];
        	$theDescriptionItems = &$theDescription[$module]['popup'];
        }else if(isset($layout['type']) && in_array($layout['type'], ['PopupMenuBarItem', 'PopupMenuItem'])){
        	$theDescription[$module]['popup'] = ['type' => 'DropDownMenu'];
        	$theDescriptionItems = &$theDescription[$module]['popup'];
        }else{
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
                unset($theDescriptionItems);
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
    
    private function pageCustomDialogDescription($customValues){
       return [
           'title'   => $this->tr('pagecustomization'),
           'paneDescription' => [
       	   		'id' => 'tukos_page_custom_dialog',
           		'widgetsDescription' => [
                   'hideLeftPane' => Widgets::storeSelect(Widgets::complete(
                        ['storeArgs' => ['data' => Utl::idsNamesStore(['YES', 'NO'], $this->tr)], 'title' => $this->tr('hideleftpane'),
                         'onWatchLocalAction' => ['value' => ['hideLeftPane' => ['localActionStatus' => [
                                        'triggers' => ['user' => true],
                                        'action' => "domstyle.set('leftPanel', 'display', (newValue === 'NO' ? 'block' : 'none'));" .
                                                    "setTimeout(function(){registry.byId('appLayout').resize();}, 100);" .
                                                    "return true;",
                    ]]]]])),
                	'leftPaneWidth' => Widgets::textBox(Widgets::complete(['label' => $this->tr('Leftpanewidth'), 'onWatchLocalAction' => 
                			['value' => ['leftPaneWidth' => ['localActionStatus' => [
                					'triggers' => ['user' => true],
                					'action' => "domstyle.set('leftPanel', 'width', newValue);" .
                								"Pmg.addCustom('leftPaneWidth', newValue);" .
                                                "setTimeout(function(){registry.byId('appLayout').resize();}, 100);" .
                                                "return true;",
                			]]]]
                	])),
           			'panesConfig' => Widgets::simpleDgrid(Widgets::complete(
           				['label' => $this->tr('panes'), 'storeType' => 'MemoryTreeObjects', 'storeArgs' => ['idProperty' => 'idg'], 'initialId' => true, 'style' => ['width' => '500px'],
                         'colsDescription' => [
           					'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
           					//'name'  => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('panename')]]), false),
           					'name'  => Widgets::description(Widgets::storeSelect([
           						'edit' => ['storeArgs' => ['data' => $this->accordionStoreData], 'label' => $this->tr('panename')],
           					]), false),
                         	'selected' => Widgets::description(Widgets::checkBox([
           						//'storeedit' => ['editOn' => 'click'], 
           						'edit' => ['label' => $this->tr('selected'), 'onChangeLocalAction' => ['selected' => ['localActionStatus' => [
           											"if (newValue){\n" .
           												"var grid = sWidget.grid, collection = grid.collection, idp = collection.idProperty, dirty = grid.dirty;\n" .
           												"console.log('newValue is true');\n" .
           												"collection.fetchSync().forEach(function(item){\n" .
           											    	"var idv = item[idp], dirtyItem = dirty[idv];\n" .
           											    	"if ((dirtyItem && dirtyItem.hasOwnProperty('selected') && dirtyItem.selected) || item.selected){\n" .
           											        	"grid.updateDirty(idv, 'selected', false);\n" .
           											    	"}\n" .
           												"})\n;" .
           											"}\n" .
           											"return true;\n"
           						]]]],
           					]), false),
                         	'present' => Widgets::description(Widgets::storeSelect([
                         		'edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['YES', 'NO'], $this->tr)], 'label' => $this->tr('presentpane')]
                         	]), false),
		                	'id' => Widgets::description(Widgets::objectSelect([
		                		'edit' => ['label' => $this->tr('id'), 'object' => 'calendars'], 
		                		'storeedit' => ['canEdit' => '(function(item, cellValue){console.log("I am here");if(item.hasId){return true;}else{return false;}})']
		                	]), false),
                         ]])),
           		       'fieldsMaxSize' => Widgets::textBox(Widgets::complete(['label' => $this->tr('Fieldsmaxsize'), 'onWatchLocalAction' =>
               		        ['value' => ['maxFieldsSize' => ['localActionStatus' => [
               		            'triggers' => ['user' => true],
               		            'action' => "Pmg.addCustom('fieldsmaxsize', newValue); return true;"
               		        ]]]]
               		    ])),
           				'cancel' => ['type' => 'TukosButton', 'atts' => ['label' => $this->tr('close'), 'onClickAction' => 'this.pane.close();']],
                        'action' => ['type' => 'TukosButton', 'atts' => ['label' => $this->tr('save'), 'onClickAction' => 
                        	"this.pane.serverAction( {object: 'users', view: 'NoView', action: 'PageCustomSave'}, {'excludeWidgets': ['cancel', 'action']});this.pane.close();" 
                        ]],
                ],
                'layout' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                    'contents' => [
                       'row1' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 100],
                            'widgets' => ['hideLeftPane', 'leftPaneWidth', 'panesConfig', 'fieldsMaxSize'],
                        ],
                       'row2' => [
                            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                            'widgets' => ['cancel', 'action'],
                        ],
                    ],
                ], 
           		'onOpenAction' =>
           			"var setValueOf = lang.hitch(this, this.setValueOf);\n" .
           			"this.watchOnChange = false;\n" .
           			"utils.forEach(Pmg.getCustom(''), function(value, widgetName){\n" .
           				"setValueOf(widgetName, value)\n" .
           			"});" .
           			"this.watchOnChange = true;\n"
           ],                     
        ];
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
        $template->jsTukosDir = Tfk::moduleLocation('tukos');//jsFullDir('tukos');
        $template->dojoDir = Tfk::dojoBaseLocation();//jsFullDir('');
        $template->dojoFixesDir = Tfk::jsFullDir('dojoFixes');
        $template->redipsDir = Tfk::jsFullDir('redips');
        $template->language = $translatorsStore = Tfk::$registry->get('translatorsStore')->getLanguage();
        $template->loadingMessage = $this->tr('Loading') . '...';

        $this->pageManagerArgs['headerContent'] = Utl::substitute(
          	'<table width="100%"><tr><td> ${buttons}<b>Tukos 2.0</b><span id="tukosHeaderLoading"></span></td><td align="center"><b><i>${header} ${ownerorg}</i></b></td><td align="right">${welcome}</td></table>', [
          		'buttons' => empty($this->pageManagerArgs['accordionDescription']) ? '' : $this->leftPaneButtons, 'header' => $this->tr(Tfk::$registry->appName . 'HeaderBanner', 'none'), 
          		'ownerorg' => $this->tr('ownerorganization', 'none'), 'welcome' => $this->welcomeConnect(Tfk::$registry->pageUrl . 'auth/logout')]
        );
        $this->pageManagerArgs['menuBarDescription'] = $this->menuBarDescription($modulesMenuLayout);

        $this->pageManagerArgs['userEditUrl'] = ['object' => 'users', 'view' => 'Edit', 'mode' => 'Tab', 'action' => 'Tab', 'query' => ['id' => $this->user->id()]];

        $this->pageManagerArgs['pageCustomization'] = $this->user->PageCustomization();
        if (isset($this->pageManagerArgs['pageCustomization']['panesConfig'])){
        	SUtl::addItemsIdCols($this->pageManagerArgs['pageCustomization']['panesConfig'], ['id']);
        }
        $this->pageManagerArgs['pageCustomDialogDescription'] = $this->pageCustomDialogDescription($this->pageManagerArgs['pageCustomization']);

        Feedback::add($this->tr('svrexectime') . (microtime(true) - Tfk::$startMicroTime));
        $this->pageManagerArgs = array_merge($this->pageManagerArgs, ['extras' => Tfk::getExtras()],
            array_filter(['feedback' => Feedback::get(), 'extendedIds' => SUtl::translatedExtendedIdCols(), 'messages' => Tfk::$registry->get('translatorsStore')->getSetMessages('page')])
        );

        $template->pageManagerArgs = json_encode($this->pageManagerArgs);
        
        $finder = $template->getTemplateFinder();
        $finder->setPaths([dirname(__FILE__)]);
        
        $this->dialogue->response->setContent (Tfk::$registry->get('translatorsStore')->substituteTranslations($template->fetch('PageTemplate.php')));
    }
}
?>
