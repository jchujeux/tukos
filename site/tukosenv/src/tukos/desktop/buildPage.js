define(["dojo/_base/lang", "dojo/dom", "dojo/dom-style", "dojo/ready", "dijit/registry", "dijit/layout/BorderContainer", "dijit/layout/TabContainer", "dijit/layout/ContentPane", "dijit/layout/AccordionContainer",
	"tukos/desktop/NavigationMenu", "tukos/TabsManager", "tukos/AccordionManager", "tukos/TabOnClick", "tukos/PageManager"], 
function (lang, dom, domStyle, ready, registry, BorderContainer, TabContainer, ContentPane, AccordionContainer, NavigationMenu, TabsManager, AccordionManager, TabOnClick, Pmg) {
	return {
		initialize: function(){
			var self = this, obj = Pmg.cache, appLayout = new BorderContainer({design: 'sidebar'}, "appLayout");
			var pageCustomization = obj.pageCustomization || {}, hideLeftPane = pageCustomization.hideLeftPane === 'YES', leftPaneWidth = pageCustomization.leftPaneWidth || "12%", panesConfig = pageCustomization.panesConfig || [],
				newPageCustomization = obj.newPageCustomization = lang.clone(pageCustomization);;
			var leftAccordion = new AccordionContainer({id: 'leftPanel', region: "left", 'class': "left", splitter: true, style: {width: leftPaneWidth, padding: "0px", display: (hideLeftPane ? "none" : "block")}});
			appLayout.addChild(leftAccordion);
			this.accordion   = new AccordionManager({container: leftAccordion});
			var contentHeader = new ContentPane({id: 'tukosHeader', region: "top", 'class': "edgePanel", style: "padding: 0px;", content: obj.headerContent});
			contentHeader.on('contextmenu', lang.hitch(this, this.contextMenuCallback));
			contentHeader.addChild(new NavigationMenu(obj.menuBarDescription));
			appLayout.addChild(contentHeader);

			var contentTabs = new TabContainer({id: "centerPanel", region: "center", tabPosition: "top", 'class': "centerPanel", style: "width: 100%; height: 100%; padding: 0px"});
			appLayout.addChild( contentTabs );

			Pmg.tabs = new TabsManager({container: contentTabs, tabsDescription: obj.tabsDescription});
			var userTabLink = new TabOnClick({url: obj.userEditUrl}, "pageusername");
			      
			var newPanesConfig = [], rowId = 1;
			obj.accordionDescription.forEach(function(description, key){
				var paneId = description['id'], paneConfigKey, newPaneConfig;
				panesConfig.some(function(paneConfig, key){
					if(paneConfig.name === paneId){
						paneConfigKey = key;
						return true;
					};
				});
				newPaneConfig = paneConfigKey ? panesConfig[paneConfigKey] : {name: paneId, present: 'YES'};
				if (description.config){
					newPaneConfig = lang.mixin(newPaneConfig, description.config);
				}
				newPaneConfig.rowId = rowId;
				newPanesConfig.push(newPaneConfig);
				rowId += 1;
			});
			if (newPanesConfig.length > 0){
				Pmg.addCustom('panesConfig', newPanesConfig);
			}
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
			               	newPageCustomization.hideLeftPane = 'NO';
			               	leftPaneButton.set("iconClass", "ui-icon tukos-left-superarrow");
			               	leftPaneMaxButton.set("iconClass", "ui-icon tukos-right-superarrow");
			               	self.resizeLayoutPanes(true);
			               });
			           }else{
			           	domStyle.set('leftPanel', 'display', 'none');
			           	newPageCustomization.hideLeftPane = 'YES';
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
			               	newPageCustomization.hideLeftPane = 'NO';
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
			           	self.resizeLayoutPanes(true)
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
				       	obj.newPageCustomization.width = newWidth;
				       	//console.log('splitter was called')
				       });
				   	Pmg.setFeedback(obj.feedback);
			   });
			});
	        Pmg.mayHaveNavigator = function(){
	        	return registry.byId('showHideLeftPane');
	        };
			Pmg.showInNavigator = lang.hitch(this, this.showInNavigator);
			//Pmg.alert = lang.hitch(this, this.alert);
	        Pmg.editorGotoTab = function(target, event){
	            event.stopPropagation();
	        	Pmg.tabs.gotoTab(target);
	        };
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
            	var panesConfig = Pmg.cache.newPageCustomization.panesConfig || [], self = this, selectedAccordionPane;
            	Pmg.cache.accordionDescription.forEach(function(description, key){
                	var paneConfig = panesConfig[key] || {}, selected = paneConfig.selected;
                	if (!(paneConfig.present === 'NO')){
                		var pane = self.accordion.create(description);
                    	if (selected){
                    		selectedAccordionPane = pane;
                    	}
                	}
                });  
            	if (!ignoreSelected && selectedAccordionPane){
            		ready(function(){self.accordion.gotoPane(selectedAccordionPane);});
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
                	newPageCustomization.hideLeftPane = 'NO';
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
                    	this.accordion.gotoPane(navigatorPane);
            		}));
            	}else{
                	navigatorPane.form.getWidget('tree').showItem({id: id, object: Pmg.objectName(id)});
                	this.accordion.gotoPane(navigatorPane);                		
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
