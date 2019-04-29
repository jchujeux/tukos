define(["dojo/ready", "dojo/_base/lang", "dojo/_base/Deferred", "dojo/dom", "dojo/dom-style", "dojo/string", "dojo/request", "dijit/_WidgetBase", "dijit/form/_FormValueMixin", "dijit/form/_CheckBoxMixin", "dijit/registry", "dojo/json",
		"tukos/_WidgetsExtend", "tukos/_WidgetsFormExtend"],
function(ready, lang, Deferred, dom, domStyle, string, request, _WidgetBase, _FormValueMixin, _CheckboxMixin, registry, JSON, _WidgetsExtend, _WidgetsFormExtend){
    var stores, tabs,
        objectsTranslations = {}, objectsUntranslations = {},
        urlTemplate = '${dialogueUrl}${object}/${view}/${mode}/${action}';
		lang.extend(_WidgetBase, _WidgetsExtend);//for this to work in all cases, no require for a widget should be made before this statement executes, above in PageManager, and in modules required in evalUtils (which _WidgetsExtend depends on)
		lang.extend(_FormValueMixin, _WidgetsFormExtend);
		lang.extend(_CheckboxMixin, _WidgetsFormExtend);
	return {
		initializeTukosForm: function(obj){
			tukos = {Pmg: this}; // to make editorGotoUrl and editorGotoTab visible in LinkDialog and TukosLinkDialog
			this.cache = obj;
			this.cache.messages = this.cache.messages || {};				
	   },
	   initialize: function(obj) {
            tukos = {Pmg: this}; // to make editorGotoUrl and editorGotoTab visible in LinkDialog and TukosLinkDialog
            this.cache = obj;
            this.cache.extras = this.cache.extras || {};
            this.cache.messages = this.cache.messages || {};
            var self = this;
            Date.prototype.toJSON = function(){
                return dojo.date.locale.format(this, {formatLength: "long", selector: "date", datePattern: 'yyyy-MM-dd HH:mm:ss'});
            };
            require([obj.isMobile ? "tukos/mobile/buildPage" : "tukos/desktop/buildPage", "tukos/StoresManager", "tukos/utils"], function(buildPage, StoresManager, utils){
            	stores = new StoresManager();
            	buildPage.initialize();
                self.requestUrl = function(urlArgs){//functions depending on utils locateed here so that utils can be located in the require above and then can depend on PageManager
                    return string.substitute(urlTemplate, {dialogueUrl: self.getItem('dialogueUrl'), object: urlArgs.object, view: urlArgs.view, mode: urlArgs.mode || 'Tab', action: urlArgs.action}) + '?' + utils.join(urlArgs.query);
                };
                self.addExtendedIdsToCache = function(newExtendedIds){
                    self.cache.extendedIds = utils.merge(self.cache.extendedIds, newExtendedIds);
                };
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
		alert: function(atts, eventHandle){
		    return this._dialogConfirm(atts, 'alert', eventHandle);
		},
        _dialogConfirm: function(atts, mode, eventHandle){
			var self = this;
			if (eventHandle){
				eventHandle.pause();
			}
			if (this.dialogConfirm){
				return this.dialogConfirm.show(atts, mode).then(
					function(response){
						if (eventHandle){eventHandle.resume()};
						return response;
					},
					function(response){
						if (eventHandle){eventHandle.resume()};
						return response;
					}
				);
			}else{
				var dfd = new Deferred();
				require([this.cache.isMobile ? "tukos/mobile/DialogConfirm" : "tukos/desktop/DialogConfirm"], function(DialogConfirm){
					self.dialogConfirm = new DialogConfirm({style: {backgroundColor: 'DarkGrey'}});
					self.dialogConfirm.show(atts, mode).then(
						function(){
							dfd.resolve(true);
							if (eventHandle){eventHandle.resume()};
						},
						function(){
							dfd.cancel(true);
							if (eventHandle){eventHandle.resume()};
						});
				});
				return dfd;
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
/*
        requestUrl: function(urlArgs){
            return string.substitute(urlTemplate, {dialogueUrl: this.getItem('dialogueUrl'), object: urlArgs.object, view: urlArgs.view, mode: urlArgs.mode || 'Tab', action: urlArgs.action}) + '?' + utils.join(urlArgs.query);
        },
*/
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
        loading: function(title, longMessage){
        	var url = require.toUrl('tukos/resources/images/loadingAnimation.gif');
        	return title + '&nbsp;' + '<img alt="Embedded Image" src="' + url + '"/> ' + (longMessage ? (this.message('loading') + '...') : '');
        },
        refresh: function(tabOrAccordion, action, data, keepOptions){
        	return this[tabOrAccordion.isAccordion()? 'accordion' : 'tabs'].refresh(action, data, keepOptions);
        },
        serverDialog: function(urlArgs, options, feedback, returnDeferred){//if returnDeferred is true, the returnedDfD.response.getHeader() will be available to extract header information
            var self = this, isObjectFeedback = typeof feedback === 'object', defaultFeedback = isObjectFeedback ? feedback.defaultFeedback : feedback;
            options = lang.mixin({method: 'POST', timeout: 180000, handleAs: 'json'},  options);
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
                    if (response.extendedIds){
                        self.addExtendedIdsToCache(response.extendedIds);
                    }
                    self.addExtrasToCache(response.extras);
                    if (defaultFeedback !== false){
                        self.addFeedback(response['feedback'], defaultFeedback);
                    }
                    if (isObjectFeedback){
                    	set(att, attValue);
                    }
                    return response;
                },
                function(error){
                    self.addFeedback(self.message(failedOperation)  + ': ' + error.message);
                    if (isObjectFeedback){
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
            var newFeedback = (serverFeedback != null && typeof serverFeedback == "object") ? serverFeedback.join("\n") : (serverFeedback  || clientFeedback || this.message('Ok')),
                  currentTab = this.tabs ? this.tabs.currentPane() : false, self = this;;
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
        namedId: function(id){
            return (id && id != 0 ? (this.cache.extendedIds[id] ? this.cache.extendedIds[id].name + '(' + id + ')' : '(' + id + ')') : '');
        },
        itemName: function(id){
            return (id && id != 0 ? (this.cache.extendedIds[id] ? this.cache.extendedIds[id].name : '') : '');
        },
        objectName: function(id){
            return (id && id != 0 ? (this.cache.extendedIds[id] ? this.cache.extendedIds[id].object : '') : '');
        },
/*
        addExtendedIdsToCache: function(newExtendedIds){
            this.cache.extendedIds = utils.merge(this.cache.extendedIds, newExtendedIds);
        },
*/
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
