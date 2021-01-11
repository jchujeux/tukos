define(["dojo/ready", "dojo/_base/lang", "dojo/_base/Deferred", "dojo/string", "dojo/request", "dijit/_WidgetBase", "dijit/form/_FormValueMixin", "dijit/form/_CheckBoxMixin", "dijit/registry", 
		"dojo/json", "dojo/date/locale", "dgrid/List", "tukos/_WidgetsExtend", "tukos/_WidgetsFormExtend", "tukos/utils"],
function(ready, lang, Deferred, string, request, _WidgetBase, _FormValueMixin, _CheckboxMixin, registry, JSON, dojoDateLocale, List, _WidgetsExtend, _WidgetsFormExtend, utils){
    var stores, tabs, openedBrowserTabs = {},
        objectsTranslations = {}, objectsUntranslations = {},
        urlTemplate = '${dialogueUrl}${object}/${view}/${mode}/${action}';
		lang.extend(_WidgetBase, _WidgetsExtend);//for this to work in all cases, no require for a widget should be made before this statement executes, above in PageManager, and in modules required in evalUtils (which _WidgetsExtend depends on)
		lang.extend(List, _WidgetsExtend);
		lang.extend(_FormValueMixin, _WidgetsFormExtend);
		lang.extend(_CheckboxMixin, _WidgetsFormExtend);
	return {
		initializeTukosForm: function(obj){
			tukos = {Pmg: this};
			this.cache = obj;
			this.cache.messages = this.cache.messages || {};				
	   },
	   initializeForm: function(obj){
		   this.cache = obj;
		   this.cache.messages = this.cache.messages || {};
           var self = this;
           Date.prototype.toJSON = function(){
               return dojoDateLocale.format(this, {formatLength: "long", selector: "date", datePattern: 'yyyy-MM-dd HH:mm:ss'});
           };
           require([obj.isMobile ? "tukos/mobile/buildForm" : "tukos/desktop/buildForm", "tukos/StoresManager"], function(buildForm, StoresManager){
	           stores = new StoresManager();
	           buildForm.initialize();
	           self.requestUrl = function(urlArgs){//functions depending on utils located here so that utils can be located in the require above and then can depend on PageManager
	               return string.substitute(urlTemplate, {dialogueUrl: self.get('dialogueUrl'), object: urlArgs.object, view: urlArgs.view, mode: urlArgs.mode || 'Tab', action: urlArgs.action}) + '?' + utils.join(urlArgs.query);
	           };
           });
	   },
	   initialize: function(obj) {
            tukos = {Pmg: this}; // to make editorGotoUrl and editorGotoTab visible in LinkDialog and TukosLinkDialog
            this.cache = obj;
            this.cache.extras = this.cache.extras || {};
            this.cache.messages = this.cache.messages || {};
            var self = this;
            Date.prototype.toJSON = function(){
                return dojoDateLocale.format(this, {formatLength: "long", selector: "date", datePattern: 'yyyy-MM-dd HH:mm:ss'});
            };
            require([obj.isMobile ? "tukos/mobile/buildPage" : "tukos/desktop/buildPage", "tukos/StoresManager"], function(buildPage, StoresManager){
            	stores = new StoresManager();
            	buildPage.initialize();
                self.serverTranslations = function(expressions, actionModel){
                    var results = {}, actionModel = actionModel || 'GetTranslations';
                    return self.serverDialog({object: 'users', view: 'NoView', action: 'Get', query:{params: {actionModel: actionModel}}}, {data: expressions}, self.message('actionDone')).then(function (response){
                            utils.forEach(response.data, function(translations, objectName){
                                var objectUntranslations = objectsUntranslations[objectName] || (objectsUntranslations[objectName] = {}), objectTranslations = objectsTranslations[objectName] || (objectsTranslations[objectName] = {});
                                results[objectName] = {};
                                utils.forEach(translations, function(translation, expression){
                                    objectTranslations[expression] = translation;
                                    objectUntranslations[translation] = expression;
                                    if (actionModel === 'GetTranslations'){
                                        results[objectName][expression] = translation;
                                    }else{
                                        results[objectName][translation] = expression;
                                    }
                                });
                            });
                            return results;
                    });
                };
            });
        },
		isMobile: function(){
			return this.cache.isMobile;
		},
        confirm: function(atts, eventHandle){
		    return this._dialogConfirm(atts, 'confirm', eventHandle);
		},
		confirmForgetChanges: function(changes){
			var changesMessage = changes ? (changes.widgets ? (changes.customization ? 'fieldsAndCustom' : 'fields') : (changes.customization ? 'custom' : '')) : 'fieldsOrCustom';
			if (changesMessage === ''){
				return true;
			}else{
				return this.confirm({title: this.message(changesMessage + 'HaveBeenModified'), content: this.message('sureWantToForget')});
			}
		},
		alert: function(atts, eventHandle){
		    return this._dialogConfirm(atts, 'alert', eventHandle);
		},
        _dialogConfirm: function(atts, mode, eventHandle){
			var self = this;
			if (eventHandle){
				eventHandle.pause();
			}
			if (this.dialogConfirm){
				return this._confirmDialogShow(atts, mode, eventHandle, this.dialogConfirm);
			}else{
				var dfd = new Deferred();
				require([this.cache.isMobile ? "tukos/mobile/DialogConfirm" : "tukos/desktop/DialogConfirm"], function(DialogConfirm){
					self.dialogConfirm = new DialogConfirm({style: {backgroundColor: 'DarkGrey'}});
					self._confirmDialogShow(atts, mode, eventHandle, self.dialogConfirm, dfd);
				});
				return dfd;
			}
		},
		_confirmDialogShow: function(atts, mode, eventHandle, dialogConfirm, dfd){
			var promise = dialogConfirm.show(atts, mode);
			promise.then(
				function(response){
					if (dfd){dfd.resolve(true);}
					if (eventHandle){eventHandle.resume()};
					return response;
				},
				function(response){
					if(dfd){dfd.cancel(true);}
					if (eventHandle){eventHandle.resume()};
					return response;
				}
			);
			return promise;			
		},
        get: function(item){
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
            return string.substitute(urlTemplate, {dialogueUrl: this.get('dialogueUrl'), object: urlArgs.object, view: urlArgs.view, mode: urlArgs.mode || 'Tab', action: urlArgs.action}) + '?' + utils.join(urlArgs.query);
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
            if (openedBrowserTabs[url]){
            	openedBrowserTabs[url].close();
            }
        	openedBrowserTabs[url] = window.open(url, url);
            event.stopPropagation();
            return false;
        },
        loading: function(title, longMessage){
        	var url = require.toUrl('tukos/resources/images/loadingAnimation.gif');
        	return title + '&nbsp;' + '<img alt="Embedded Image" src="' + url + '"/> ' + (longMessage ? (this.message('loading') + '...') : '');
        },
        refresh: function(tabOrAccordion, action, data, keepOptions){
        	return this[tabOrAccordion.isAccordion()? 'accordion' : 'tabs'].refresh(action, data, keepOptions);
        },
        serverDialog: function(urlArgs, options, feedback, returnDeferred){//if returnDeferred is true, the returnedDfD.response.getHeader() will be available to extract header information
            var self = this, isObjectFeedback = utils.isObject(feedback), defaultFeedback = isObjectFeedback ? feedback.defaultFeedback : feedback;
            options = lang.mixin({method: 'POST', timeout: 32000, handleAs: 'json'},  options);
            if (options.data){
                options.data = JSON.stringify(options.data);
                options.method = 'POST';
            }
            if (isObjectFeedback){
            	var widget= feedback.widget, get = lang.hitch(widget, widget.get), set = lang.hitch(widget, widget.set), att = feedback.att, attValue = get(att);
            	set(att, this.loading(attValue, feedback.longMessage));
            }
            var dfdOrPromise = request(this.requestUrl(urlArgs), options, returnDeferred);
            dfdOrPromise.then(
            	function(response){
                    response = response || {};
                    self.addExtendedIdsToCache(response.extendedIds);
                    self.addMessagesToCache(response.messages, urlArgs.object);
                    self.addExtrasToCache(response.extras);
                    if (defaultFeedback !== false){
                        self.setFeedback(response['feedback'], defaultFeedback);
                    }
                    if (isObjectFeedback){
                    	set(att, attValue);
                    }
                    return response;
                },
                function(error){
                    self.setFeedback(self.message('failedOperation')  + ': ' + error.message);
                    if (isObjectFeedback){
                    	set(att, attValue);
                    }
                    return error;
                }
            );
            return dfdOrPromise;
        },
        beep: function(){
        	(this.beepCache || (this.beepCache = document.getElementById('beep'))).play();
        },
        setFeedback: function(serverFeedback, clientFeedback, separator, beep){
            if (beep){
                this.beep();
            }
            var newFeedback = (serverFeedback != null && typeof serverFeedback == "object") ? serverFeedback.join("\n") : (serverFeedback  || clientFeedback || '' /*|| this.message('Ok')*/), widget, form,
                  currentTab = this.tabs ? this.tabs.currentPane() : false, self = this;
			if (this.focusedPanel === "leftPanel"){
				console.log('focus is on left panel');
				var currentPane = this.accordion ? this.accordion.currentPane() : false, form = currentPane.form || {};
				if (form.getWidget){
					widget = form.getWidget('feedback');
				}
			}         
			if (!widget){
				form = (currentTab || {}).form || {};
				if (form.getWidget){
					widget = form.getWidget('feedback');
				}
			}
            if (widget){
                widget.set('value', separator ? widget.get('value') + separator + newFeedback : newFeedback);
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
        addFeedback: function(serverFeedback, clientFeedback, beep){
            this.setFeedback(serverFeedback, clientFeedback, "\n", beep);
        },
        namedId: function(id){
            //return (id && id != 0 ? (this.cache.extendedIds[id] ? this.cache.extendedIds[id].name + '(' + id + ')' : '(' + id + ')') : '');
            return id ? (utils.drillDown(this.cache.extendedIds, [id, 'name']) || utils.drillDown(this.cache.extras, [id, 'name'], '')) + '(' + id + ')' : '';
        },
        itemName: function(id){
            return (id && id != 0 ? (this.cache.extendedIds[id] ? this.cache.extendedIds[id].name : '') : '');
        },
        objectName: function(id, domain){
            //return (id && id != 0 ? (this.cache.extendedIds[id] ? this.cache.extendedIds[id].object : '') : '');
            var objectName = id ? (utils.drillDown(this.cache.extendedIds, [id, 'object']) || utils.drillDown(this.cache.extras, [id, 'object'])) : '';
            if (objectName && domain){
            	return utils.drillDown(this.cache.objectsDomainAliases, [objectName, domain], objectName);
            }else{
            	return objectName;
            }
        },
        addExtendedIdsToCache: function(newExtendedIds){
            this.cache.extendedIds = utils.merge(this.cache.extendedIds, newExtendedIds || []);
        },
        addMessagesToCache: function(messages, object){
        	if (!utils.empty(messages)){
            	var objectsMessagesCache = this.cache.objectsMessages || (this.cache.objectsMessages = {}), objectMessagesCache = objectsMessagesCache[object] || (objectsMessagesCache[object] = {});
            	objectMessagesCache = lang.mixin(objectMessagesCache, messages);
        	}
        },
        addExtrasToCache: function(newExtras){
        	this.cache.extras = lang.mixin(this.cache.extras, newExtras || []);
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
        message: function(key, object){
        	return object ? this.cache.objectsMessages[object][key] || this.cache.messages[key] || key : this.cache.messages[key] || key;
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
        	lang.setObject(path, value, this.cache.pageChangesCustomization);
        },
        getCustom: function(args, absentValue){
        	if (args && typeof args === 'object'){
        		var pageTukosOrUserOrChangesCustomization = 'page' + utils.capitalize(args.tukosOrUserOrChanges) + 'Customization';
        		return args.property ? this.cache[pageTukosOrUserOrChangesCustomization][args.property] : this.cache[pageTukosOrUserOrChangesCustomization];
        	}else{
            	return args ? utils.drillDown(this.cache.newPageCustomization, 'args', absentValue) : this.cache.newPageCustomization;
        	}
        },
        setCustom: function(args, value){
        	if (args && typeof args === 'object'){
        		var pageTukosOrUserOrChangesCustomization = 'page' + utils.capitalize(args.tukosOrUserOrChanges) + 'Customization';
        		this.cache[pageTukosOrUserOrChangesCustomization] = value;
        	}else{
				if (args){
					this.cache.newCustomization[args] = value;
				}else{
					this.cache.newPageCustomization = value;
				}
			}
        }
    }
});
