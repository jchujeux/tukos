define(["dojo/_base/lang", "dojo/dom", "dojo/dom-style", "dojo/ready", "dojo/when", "dijit/registry", "dijit/layout/BorderContainer", "dijit/layout/TabContainer", "dijit/layout/ContentPane", "dijit/layout/AccordionContainer",
	"dijit/focus", "tukos/desktop/NavigationMenu", "tukos/TabsManager", "tukos/AccordionManager", "tukos/TabOnClick", "tukos/utils", "tukos/widgets/WidgetsLoader", "tukos/PageManager"], 
function (lang, dom, domStyle, ready, when, registry, BorderContainer, TabContainer, ContentPane, AccordionContainer, focusUtil, NavigationMenu, TabsManager, AccordionManager, TabOnClick, utils, WidgetsLoader, Pmg) {
	return {
		initialize: function(){
			var self = this, obj = Pmg.cache, appLayout = new BorderContainer({design: 'sidebar'}, "appLayout"), pageCustomization = obj.pageCustomization || {}, hideLeftPane = pageCustomization.hideLeftPane === 'YES', 
				leftPaneWidth = pageCustomization.leftPaneWidth || "12%", panesConfig = pageCustomization.panesConfig || [], newPageCustomization = obj.newPageCustomization = lang.clone(pageCustomization),
				leftAccordion = new AccordionContainer({id: 'leftPanel', region: "left", 'class': "left", splitter: true, style: {width: leftPaneWidth, padding: "0px", display: (hideLeftPane ? "none" : "block")}});
			appLayout.addChild(leftAccordion);
			Pmg.accordion   = new AccordionManager({container: leftAccordion});
			var contentHeader = new ContentPane({id: 'tukosHeader', region: "top", 'class': "edgePanel", style: "padding: 0px;", content: obj.headerContent});
			if (Pmg.get('userRights') === 'SUPERADMIN' || Pmg.getCustom('pageCustomForAll') === 'YES' || (!Pmg.get('noPageCustomForAll') && Pmg.getCustom('pageCustomForAll') !== 'NO')){
				contentHeader.on('contextmenu', lang.hitch(this, this.contextMenuCallback));
			}
			contentHeader.addChild(new NavigationMenu(obj.menuBarDescription));
			appLayout.addChild(contentHeader);

			var contentTabs = new TabContainer({id: "centerPanel", region: "center", tabPosition: "top", 'class': "centerPanel", style: "width: 100%; height: 100%; padding: 0px"});
			appLayout.addChild( contentTabs );

			//Pmg.tabs = new TabsManager({container: contentTabs, tabsDescription: obj.tabsDescription});
			Pmg.resizeTab = function(){
				var container = Pmg.tabs.container;
				container.selectedChildWidget && container.selectedChildWidget.resize(container._containerContentBox, container._contentBox);
			}
			if (Pmg.get('userRights') !== 'RESTRICTEDUSER'){
				new TabOnClick({url: obj.userEditUrl}, "pageusername");
			}
			focusUtil.on('widget-focus', function(widget){
				var panel = focusUtil.get('activeStack')[1];
				switch (panel){
					case 'leftPanel': 
					case 'centerPanel':
						Pmg.focusedPanel = panel;
				}
			});
			obj.pageChangesCustomization = {};
			ready(function(){
			   if (!hideLeftPane){
			   	self.lazyCreateAccordion();
			   }
			   appLayout.startup();
			   var leftPaneButton = registry.byId('showHideLeftPane'), leftPaneMaxButton = registry.byId('showMaxLeftPane'), displayStatus = domStyle.get('leftPanel', 'display'), isMaximized = false;
			   if (leftPaneButton){
			       leftPaneButton.set("iconClass", displayStatus === 'none' ? "ui-icon tukos-right-arrow" : "ui-icon tukos-left-superarrow");
			       leftPaneButton.on('click', function(){
			           var displayStatus = domStyle.get('leftPanel', 'display');
			           isMaximized = false;
			           if (displayStatus === 'none'){
			           	   self.lazyCreateAccordion();
			               ready(function(){
				               	domStyle.set('leftPanel', 'width', newPageCustomization.leftPaneWidth || pageCustomization.leftPaneWidth);
				               	domStyle.set('leftPanel', 'display', 'block');
				               	//newPageCustomization.hideLeftPane = 'NO';
								Pmg.addCustom('hideLeftPane', 'NO');
				               	leftPaneButton.set("iconClass", "ui-icon tukos-left-superarrow");
				               	leftPaneMaxButton.set("iconClass", "ui-icon tukos-right-superarrow");
				               	self.resizeLayoutPanes(true);
			               });
			           }else{
			           	domStyle.set('leftPanel', 'display', 'none');
						Pmg.addCustom('hideLeftPane', 'YES');
			           	leftPaneButton.set("iconClass", "ui-icon tukos-right-arrow");
			           	leftPaneMaxButton.set("iconClass", "ui-icon tukos-right-superarrow");
			           	self.resizeLayoutPanes(false);
			           }
			       });                    	
			       leftPaneMaxButton.set("iconClass", "ui-icon tukos-right-superarrow");
			       leftPaneMaxButton.on('click', function(){
			           var displayStatus = domStyle.get('leftPanel', 'display');
			           if (displayStatus === 'none'){
			           	self.lazyCreateAccordion();
			           	ready(function(){
			               	domStyle.set('leftPanel', 'width', '80%');
			               	isMaximized = true;
			               	domStyle.set('leftPanel', 'display', 'block');
			               	//newPageCustomization.hideLeftPane = 'NO';
							Pmg.addCustom('hideLeftPane', 'NO');
			               	leftPaneMaxButton.set("iconClass", "ui-icon tukos-left-arrow");
			               	leftPaneButton.set("iconClass", "ui-icon tukos-left-superarrow");
			               	self.resizeLayoutPanes(true)
			           	});
			           }else{
				           	if (isMaximized){
				               	domStyle.set('leftPanel', 'width', newPageCustomization.leftPaneWidth || leftPaneWidth);                   		
				               	leftPaneMaxButton.set("iconClass", "ui-icon tukos-right-superarrow");
				               	isMaximized = false;
				           	}else{
				               	domStyle.set('leftPanel', 'width', '80%');                       		
				               	leftPaneMaxButton.set("iconClass", "ui-icon tukos-left-superarrow");
				               	leftPaneMaxButton.set("iconClass", "ui-icon tukos-left-arrow");
				               	isMaximized = true;
				           	}
				           	Pmg.resizeLayoutPanes(true);
			           }
			       });
			   }
			   ready(function(){
				   	var leftSplitter = appLayout.getSplitter("left");
				       domStyle.set(dom.byId('loadingOverlay'), 'display', 'none');
				   	dojo.connect(leftSplitter.domNode, 'onmouseup', function(){
				       	var newWidth = this.parentNode.children[0].style.width, leftPaneWidthWidget = registry.byId('tukos_page_custom_dialogleftPaneWidth');
				       	if (leftPaneWidthWidget){
				       		leftPaneWidthWidget.set('value', newWidth);
				       	}
				       	//obj.newPageCustomization.width = newWidth;
						Pmg.addCustom('width', newWidth);
				       	//console.log('splitter was called')
				       });
				   	Pmg.setFeedback(obj.feedback);
			   });
			});
	        Pmg.mayHaveNavigator = function(){
	        	return registry.byId('showHideLeftPane');
	        };
			Pmg.showInNavigator = lang.hitch(this, this.showInNavigator);
			Pmg.tabs = new TabsManager({container: contentTabs, tabsDescription: obj.tabsDescription});
		},
        resizeLayoutPanes: function(leftPanelDisplay){
        	var appLayout = registry.byId('appLayout');
        	domStyle.set('leftPanel', 'display', 'none');
        	domStyle.set('centerPanel', 'display', 'none');
           	appLayout.resize();
            ready(function(){
            	if (leftPanelDisplay){
            		domStyle.set('leftPanel', 'display', 'block');
            	}
            	domStyle.set('centerPanel', 'display', 'block');
               	appLayout.resize();
            	ready(function(){
                   	registry.byId('centerPanel').resize();                            		
                	if (leftPanelDisplay){
                       	registry.byId('leftPanel').resize();                            		                                		
                	}
            	});
        	});
        },
        lazyCreateAccordion: function(ignoreSelected){
            if (!this.createdAccordion){
            	var panesConfig = Pmg.cache.newPageCustomization.panesConfig || [], self = this, selectedAccordionPane, panesDescription = Pmg.cache.accordionDescription;
				utils.forEach(panesConfig, function(paneConfig){
					var theDescription = false;
					if (paneConfig.present === 'YES'){
						panesDescription.some(function(description){
							if (description.id === paneConfig.name){
								theDescription = description;
								return true;
							}
						});
					}
					if (theDescription){
						var pane = Pmg.accordion.create(theDescription);
						if (paneConfig.selected){
							selectedAccordionPane = pane;
						}
						if (paneConfig.associatedTukosId){
							pane.set('associatedtukosid', paneConfig.associatedtukosid);
						}
					}
				});
            	if (!ignoreSelected && selectedAccordionPane){
            		ready(function(){Pmg.accordion.gotoPane(selectedAccordionPane);});
            	}
            	this.createdAccordion = true;
            }
        },
        showInNavigator: function(id){
            if (domStyle.get('leftPanel', 'display') === 'none'){
            	this.lazyCreateAccordion(true);
                ready(lang.hitch(this, function(){
                	var newPageCustomization = Pmg.cache.newPageCustomization, leftPaneButton = registry.byId('showHideLeftPane'), leftPaneMaxButton = registry.byId('showMaxLeftPane');
                	domStyle.set('leftPanel', 'width', newPageCustomization.leftPaneWidth || Pmg.cache.pageCustomization.leftPaneWidth);
                	domStyle.set('leftPanel', 'display', 'block');
                	//newPageCustomization.hideLeftPane = 'NO';
					Pmg.addCustom('hideLeftPane', 'NO');
                	leftPaneButton.set("iconClass", "ui-icon tukos-left-superarrow");
                	leftPaneMaxButton.set("iconClass", "ui-icon tukos-right-superarrow");
                	this.setNavigationPane(id);
                }));
            }else{
            	this.setNavigationPane(id);
            }
        },
        setNavigationPane: function(id){
            var navigatorPane = registry.byId('pane_navigationTree');
            if (navigatorPane){
            	if (!navigatorPane.form){
            		navigatorPane.createPane();
            		ready(lang.hitch(this, function(){
                    	navigatorPane.form.getWidget('tree').showItem({id: id, object: Pmg.objectName(id)});
                    	Pmg.accordion.gotoPane(navigatorPane);
            		}));
            	}else{
                	navigatorPane.form.getWidget('tree').showItem({id: id, object: Pmg.objectName(id)});
                	Pmg.accordion.gotoPane(navigatorPane);                		
            	}
            }else{
            	console.log('case of navigator pane not present to be done');
            }
        	registry.byId('appLayout').resize();
        },
        contextMenuCallback: function(evt){
            evt.preventDefault();
            evt.stopPropagation();
            if (! this.pageCustomDialog){
                var self = this;
                require(["tukos/TukosTooltipDialog"], function(TukosTooltipDialog){
                    self.pageCustomDialog = new TukosTooltipDialog(Pmg.cache.pageCustomDialogDescription);
                    ready(function(){
                        self.pageCustomDialog.open({x: evt.clientX, y: evt.clientY});
                    });
                });
            }else{
                this.pageCustomDialog.open({x: evt.clientX, y: evt.clientY});
            }
        }
	}
});
