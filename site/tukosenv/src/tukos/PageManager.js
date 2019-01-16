define(["dojo/ready", "dojo/_base/lang", "dojo/dom", "dojo/dom-style", "dojo/string", "dojo/request", "dijit/_WidgetBase", "dijit/form/_FormValueMixin", "dijit/form/_CheckBoxMixin", "dijit/registry", "dojo/json", "tukos/utils",
		"tukos/_WidgetsExtend", "tukos/_WidgetsFormExtend", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"],
    function(ready, lang, dom, domStyle, string, request, _WidgetBase, _FormValueMixin, _CheckboxMixin, registry, JSON, utils, _WidgetsExtend, _WidgetsFormExtend, messages){
    var stores,
        tabs,
        objectsTranslations = {}, objectsUntranslations = {},
        urlTemplate = '${dialogueUrl}${object}/${view}/${mode}/${action}';
		lang.extend(_WidgetBase, _WidgetsExtend);//for this to work in all cases, no require for a widget should be made before this statement executes, above in PageManager, and in modules required in evalUtils (which _WidgetsExtend depends on)
		lang.extend(_FormValueMixin, _WidgetsFormExtend);
		lang.extend(_CheckboxMixin, _WidgetsFormExtend);
		return {
        initialize: function(obj) {
            tukos = {Pmg: this}; // to make editorGotoUrl and editorGotoTab visible in LinkDialog and TukosLinkDialog
            this.cache = obj;
            this.cache.extras = this.cache.extras || {};
            this.cache.messages = this.cache.messages || {};
            var self = this;
            Date.prototype.toJSON = function(){
                return dojo.date.locale.format(this, {formatLength: "long", selector: "date", datePattern: 'yyyy-MM-dd HH:mm:ss'});
            };
            require(["dijit/layout/BorderContainer", "dijit/layout/TabContainer", "dijit/layout/ContentPane", "dijit/layout/AccordionContainer", "tukos/StoresManager", "tukos/NavigationMenu",
                     "tukos/TabsManager", "tukos/AccordionManager", "tukos/TabOnClick", "dojo/domReady!"], 
            function(BorderContainer, TabContainer, ContentPane, AccordionContainer, StoresManager, NavigationMenu, TabsManager, AccordionManager, TabOnClick){
                stores = new StoresManager();
                var appLayout = new BorderContainer({design: 'sidebar'}, "appLayout");

                var pageCustomization = obj.pageCustomization || {}, hideLeftPane = pageCustomization.hideLeftPane === 'YES', leftPaneWidth = pageCustomization.leftPaneWidth || "12%", panesConfig = pageCustomization.panesConfig || [],
                	newPageCustomization = obj.newPageCustomization = lang.clone(pageCustomization);;
                var leftAccordion = new AccordionContainer({id: 'leftPanel', region: "left", 'class': "left", splitter: true, style: {width: leftPaneWidth, padding: "0px", display: (hideLeftPane ? "none" : "block")}});
                appLayout.addChild(leftAccordion);
                self.accordion   = new AccordionManager({container: leftAccordion});

                var contentHeader = new ContentPane({id: 'tukosHeader', region: "top", 'class': "edgePanel", style: "padding: 0px;", content: obj.headerContent});
                contentHeader.on('contextmenu', lang.hitch(self, self.contextMenuCallback));
                contentHeader.addChild(new NavigationMenu(obj.menuBarDescription));
                appLayout.addChild(contentHeader);

                var contentTabs = new TabContainer({id: "centerPanel", region: "center", tabPosition: "top", 'class': "centerPanel", style: "width: 100%; height: 100%; padding: 0px"});
                appLayout.addChild( contentTabs );

                self.tabs = new TabsManager({container: contentTabs});
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
                	self.addCustom('panesConfig', newPanesConfig);
                }
                var tabArray = [];
                for (var i in obj.tabsDescription){
                    tabArray[i] = self.tabs.create(obj.tabsDescription[i]);
                };
                ready(function(){
                    if (!hideLeftPane){
                    	self.lazyCreateAccordion();
                    }
                	appLayout.startup();
                    var leftPaneButton = registry.byId('showHideLeftPane'), leftPaneMaxButton = registry.byId('showMaxLeftPane'), displayStatus = domStyle.get('leftPanel', 'display');
                    if (leftPaneButton){
                        leftPaneButton.set("iconClass", displayStatus === 'none' ? "ui-icon tukos-right-arrow" : "ui-icon tukos-left-superarrow"), isMaximized = false;;
                        leftPaneButton.on('click', function(){
                            var displayStatus = domStyle.get('leftPanel', 'display');
                            if (displayStatus === 'none'){
                            	self.lazyCreateAccordion();
                                ready(function(){
                                	domStyle.set('leftPanel', 'width', newPageCustomization.leftPaneWidth || pageCustomization.leftPaneWidth);
                                	domStyle.set('leftPanel', 'display', 'block');
                                	newPageCustomization.hideLeftPane = 'NO';
                                	leftPaneButton.set("iconClass", "ui-icon tukos-left-superarrow");
                                	leftPaneMaxButton.set("iconClass", "ui-icon tukos-right-superarrow");
                                	registry.byId('appLayout').resize();
                                	//ready(function(){//to get the comments / details column in the search accordion to display
                                		registry.byId('leftPanel').resize();
                                	//})
                                });
                            }else{
                            	domStyle.set('leftPanel', 'display', 'none');
                            	newPageCustomization.hideLeftPane = 'YES';
                            	leftPaneButton.set("iconClass", "ui-icon tukos-right-arrow");
                            	leftPaneMaxButton.set("iconClass", "ui-icon tukos-right-superarrow");
                            	ready(function(){
                            		registry.byId('appLayout').resize();
                                	registry.byId('centerPanel').resize(); 
                            	});
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
    	                        	registry.byId('appLayout').resize();
    	                        	registry.byId('leftPanel').resize();
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
                            	registry.byId('appLayout').resize();
                            	registry.byId('centerPanel').resize();
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
                    });
                    if (obj.focusedTab){
                            contentTabs.selectChild(tabArray[obj.focusedTab]);
                    }
                });
            });
        },
        lazyCreateAccordion: function(ignoreSelected){
            if (!this.createdAccordion){
            	var panesConfig = this.cache.newPageCustomization.panesConfig || [], self = this, selectedAccordionPane;
            	this.cache.accordionDescription.forEach(function(description, key){
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
                	var newPageCustomization = this.cache.newPageCustomization, leftPaneButton = registry.byId('showHideLeftPane'), leftPaneMaxButton = registry.byId('showMaxLeftPane');
                	domStyle.set('leftPanel', 'width', newPageCustomization.leftPaneWidth || this.cache.pageCustomization.leftPaneWidth);
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

        mayHaveNavigator: function(){
        	return registry.byId('showHideLeftPane');
        },

        setNavigationPane: function(id){
            var navigatorPane = registry.byId('pane_navigationTree');
            if (navigatorPane){
            	if (!navigatorPane.form){
            		navigatorPane.createPane();
            		ready(lang.hitch(this, function(){
                    	navigatorPane.form.getWidget('tree').showItem({id: id, object: this.objectName(id)});
                    	this.accordion.gotoPane(navigatorPane);
            		}));
            	}else{
                	navigatorPane.form.getWidget('tree').showItem({id: id, object: this.objectName(id)});
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
                    self.pageCustomDialog = new TukosTooltipDialog(self.cache.pageCustomDialogDescription);
                    ready(function(){
                        self.pageCustomDialog.open({x: evt.clientX, y: evt.clientY});
                    });
                });
            }else{
                this.pageCustomDialog.open({x: evt.clientX, y: evt.clientY});
            }
        },
        getItem: function(item){
            return this.cache[item];
        },
        
        setCopiedCell: function(copiedCell){
            this.copiedCell = copiedCell;
        },

        getCopiedCell: function(){
            return this.copiedCell;
        },

        store: function(args){
            return stores.get(args);
        },
        requestUrl: function(urlArgs){
            return string.substitute(urlTemplate, {dialogueUrl: this.getItem('dialogueUrl'), object: urlArgs.object, view: urlArgs.view, mode: urlArgs.mode || 'Tab', action: urlArgs.action}) + '?' + utils.join(urlArgs.query);
        },
        openExternalUrl: function(url){//deprecated - to eliminate from existing editor content if present
            window.open(url);
            return false;
        },

        gotoExternalUrl: function(url, event){//deprecated - to eliminate from existing editor content
            window.open(url, url);
            event.stopPropagation();
            return false;
        },

        editorGotoUrl: function(url, event){
            window.open(url, url);
            event.stopPropagation();
            return false;
        },

        editorGotoTab: function(target, event){
            event.stopPropagation();
        	this.tabs.gotoTab(target);
        },

        refresh: function(tabOrAccordion, action, data, keepOptions){
        	return this[tabOrAccordion.isAccordion()? 'accordion' : 'tabs'].refresh(action, data, keepOptions);
        },
        
        loading: function(title, longMessage){
        	var url = require.toUrl('tukos/resources/images/loadingAnimation.gif');
        	return title + '&nbsp;' + '<img alt="Embedded Image" src="' + url + '"/> ' + (longMessage ? (messages.loading + ' ...') : '');
        },
        
        serverDialog: function(urlArgs, options, feedback, returnDeferred){//if returnDeferred is true, the returnedDfD.response.getHeader() will be available to extract header information
            //this.setFeedback(messages.actionDoing);
            var self = this, objectFeedback = typeof feedback === 'object', defaultFeedback = objectFeedback ? feedback.defaultFeedback : feedback;
            options = lang.mixin({method: 'POST', timeout: 180000, handleAs: 'json'},  options);
            if (options.data){
                options.data = JSON.stringify(options.data);
                options.method = 'POST';
            }
            if (objectFeedback){
            	var widget= feedback.widget, get = lang.hitch(widget, widget.get), set = lang.hitch(widget, widget.set), att = feedback.att, attValue = get(att);
            	set(att, this.loading(attValue, feedback.longMessage));
            }
            var dfdOrPromise = request(this.requestUrl(urlArgs), options, returnDeferred);
            dfdOrPromise.then(
                 function(response){
                    if (response.extendedIds){
                        self.addExtendedIdsToCache(response.extendedIds);
                    }
                    self.addExtrasToCache(response.extras);
                    if (defaultFeedback !== false){
                        self.addFeedback(response['feedback'], defaultFeedback);
                    }
                    if (objectFeedback){
                    	set(att, attValue);
                    }
                    return response;
                },
                function(error){
                    self.addFeedback(messages.failedOperation + '\n Server message is: ' + error.message);
                    if (objectFeedback){
                    	set(att, attValue);
                    }
                    return error;
                }
            );
            return dfdOrPromise;
        },

        setFeedback: function(serverFeedback, clientFeedback, separator, beep){
            if (beep){
                document.getElementById('beep').play();
            }
            var newFeedback = (serverFeedback != null && typeof serverFeedback == "object") ? serverFeedback.join("\n") : (serverFeedback  || clientFeedback || messages.nofeedback),
                  currentTab = this.tabs.currentPane(), self = this;;
            if (currentTab){
                var widget = (lang.hitch(currentTab.form, currentTab.form.getWidget))('feedback');
                if (widget){
                    widget.set('value', separator ? widget.get('value') + separator + newFeedback : newFeedback);
                }
            }
            if (this.logWidget){
            	this.logWidget.set('value', this.logWidget.get('value') + (separator ? separator : '\n') + newFeedback);
            }else{
            	if (this.logWidget !== false){
                	ready(function(){
                		var logWidget = registry.byId('tukos_loglog');
                		if (logWidget){
                			logWidget.set('value', newFeedback);
                			self.logWidget = logWidget;
                		}else{
                			self.logWidget = false;
                		}
                	});
            		
            	}
            }
        },
        
        appendFeedback: function(serverFeedback, clientFeedback, beep){
            this.setFeedback(serverFeedback, clientFeedback, ' ', beep);
        },

        addFeedback: function(serverFeedback, clientFeedback, beep){
            this.setFeedback(serverFeedback, clientFeedback, "\n", beep);
        },

        alert: function(args, blurHandle){
            require(["dijit/Dialog"], lang.hitch(this, function(Dialog){
	         	if (!this.alertDialog){
	                var dialog = this.alertDialog = new Dialog(lang.mixin(args, {onBlur: function(evt){this.hide();}}));
	                this.alertDialog.onShow = function(){// hack to counter forcing to z-index 950 in dijit/Dialog!!
	                    setTimeout(function(){
	                        domStyle.set(dialog.domNode, 'z-index', 10000);
	                    }, 100);
	                }
	            }else{
	                for (var att in args){
	                    this.alertDialog.set(att, args[att]);
	                }
	            }
	            if (blurHandle){
	                blurHandle.pause();
	                return this.alertDialog.show().then(function(response){blurHandle.resume();return response;});
	            }else{
	                return this.alertDialog.show();
	            }
            }))
        },

        namedId: function(id){
            return (id && id != 0 ? (this.cache.extendedIds[id] ? this.cache.extendedIds[id].name + '(' + id + ')' : '(' + id + ')') : '');
        },
        itemName: function(id){
            return (id && id != 0 ? (this.cache.extendedIds[id] ? this.cache.extendedIds[id].name : '') : '');
        },
        objectName: function(id){
            return (id && id != 0 ? (this.cache.extendedIds[id] ? this.cache.extendedIds[id].object : '') : '');
        },

        addExtendedIdsToCache: function(newExtendedIds){
            this.cache.extendedIds = utils.merge(this.cache.extendedIds, newExtendedIds);
        },
        addExtrasToCache: function(newExtras){
        	this.cache.extras = lang.mixin(this.cache.extras, newExtras);
        },
        getExtra: function(id){
        	return this.cache.extras[id];
        },
        namedIdExtra: function(id){
        	return (id ? (this.cache.extras[id] && this.cache.extras[id].name ? this.cache.extras[id].name + '(' + id + ')' : id) : '');
        },
        nameExtra: function(id){
        	return (id ? (this.cache.extras[id] && this.cache.extras[id].name ? this.cache.extras[id].name : id) : '');
        },
        message: function(key){
        	return this.cache.messages[key] || key;
        },
        messages: function(keys){
        	var result = {}, messages = this.cache.messages;
        	keys.forEach(function(key){
        		result[key] = messages[key] || key;
        	});
        	return result;
        },
    	messageStoreData: function(ids){
    		var store = [{id: '', name: ''}], messages = this.cache.messages;
    		ids.forEach(function(id){
    			store.push({id: id, name: messages[id] || id});
    		})
    		return store;
    	},
        serverTranslations: function(expressions, actionModel){
                var self = this, results = {}, actionModel = actionModel || 'GetTranslations';
                return this.serverDialog({object: 'users', view: 'NoView', action: 'Get', query:{params: {actionModel: actionModel}}}, {data: expressions}, messages.actionDone).then(function (response){
                        utils.forEach(response.data, function(translations, objectName){
                            var objectUntranslations = objectsUntranslations[objectName] || (objectsUntranslations[objectName] = {}), objectTranslations = objectsTranslations[objectName] || (objectsTranslations[objectName] = {});
                            results[objectName] = {};
                            utils.forEach(translations, function(translation, expression){
                                objectTranslations[expression] = translation;
                                objectUntranslations[translation] = expression;
                                if (actionModel === 'GetTranslations'){
                                    results[objectName][expression] = translation;
                                }else{
                                    results[objectName][translation/*.toLowerCase()*/] = expression;
                                }
                            });
                        });
                        return results;
                });
        },
        defaultTranslator: function(expression, objectName){
            var objectTranslations = objectsTranslations[objectName] || (objectsTranslations[objectName] = {});
            return objectTranslations[expression] || (objectTranslations[expression] = undefined);
        },

        defaultUntranslator: function(expression, objectName){
            var objectUntranslations = objectsUntranslations[objectName] || (objectsUntranslations[objectName] = {});
            return objectUntranslations[expression.toLowerCase()];
        },
        
        widgetNameTranslator: function(widgetName, objectName, form){
            var objectTranslations = objectsTranslations[objectName] || (objectsTranslations[objectName] = {});
            return objectTranslations[widgetName] || this._getWidgetNameTranslation(widgetName, objectName, form);
        },
        
        widgetNameUntranslator: function(translatedWidgetName, objectName, form){
            var objectUntranslations = objectsUntranslations[objectName] || (objectsUntranslations[objectName] = {});
            
            return objectUntranslations[translatedWidgetName] || this._getWidgetNameUntranslation(translatedWidgetName, objectName, form);
        },

        _getWidgetNameTranslation: function(widgetName, objectName, form){
            var form = form || this.tabs.objectPane(objectName, 'Edit'), objectUntranslations = objectsUntranslations[objectName] || (objectsUntranslations[objectName] = {}), objectTranslations = objectsTranslations[objectName] || (objectsTranslations[objectName] = {}),
                   translation = undefined;
            if (form){
                var widget = form.getWidget(widgetName);
                if (widget){
                	var translation = widget.label || widget.title || undefined;
                }
                if (translation){
                    objectUntranslations[translation] = widgetName;
                }
                return (objectTranslations[widgetName] = translation);
            }else{
                return translation;
            }
        },

        _getWidgetNameUntranslation: function(translatedWidgetName, objectName, form){
            var form = form || this.tabs.objectPane(objectName, 'Edit'), objectUntranslations = objectsUntranslations[objectName] || (objectsUntranslations[objectName] = {}), objectTranslations = objectsTranslations[objectName] || (objectsTranslations[objectName] = {}),
                  untranslation = undefined;
            if (form){
                form.widgetsName.some(function(widgetName){
                    var widget = form.getWidget(widgetName), translation = undefined;
                    if (widget){
                    	var translation = objectTranslations[widgetName] = form.getWidget(widgetName).title || form.getWidget(widgetName).label || undefined;
                    }
                    if (translation){
                        objectUntranslations[translation.toLowerCase()] = widgetName;
	                    if (translation.toLowerCase() === translatedWidgetName.toLowerCase()){
	                        untranslation = widgetName;
	                        return true;
	                    }
                    }
                });
            }
            return untranslation;
        },
        
        addCustom: function(path, value){
        	lang.setObject(path, value, this.cache.newPageCustomization);
        },
        
        getCustom: function(){
        	return this.cache.newPageCustomization;
        }
    }
});
