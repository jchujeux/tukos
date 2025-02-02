define(["dojo/ready", "dojo/has", "dojo/_base/lang", "dojo/_base/Deferred", "dojo/when", "dojo/string", "dojo/request", "dijit/_WidgetBase", "dijit/form/_FormValueMixin", "dijit/form/_CheckBoxMixin", "dijit/form/_ComboBoxMenuMixin", "dijit/registry", 
		"dojo/json", "dojo/date/locale", "tukos/_WidgetsExtend", "tukos/_WidgetsFormExtend", "tukos/_ComboBoxMenuMixinExtend", "tukos/utils"],
function(ready, has, lang, Deferred, when, string, request, _WidgetBase, _FormValueMixin, _CheckboxMixin, _ComboBoxMenuMixin, registry, JSON, dojoDateLocale, _WidgetsExtend, _WidgetsFormExtend, _ComboBoxMenuMixinExtend, utils){
    var stores, openedBrowserTabs = {}, windows = {}, numWindows = 0,
        urlTemplate = '${dialogueUrl}${object}/${view}/${mode}/${action}';
		lang.extend(_WidgetBase, _WidgetsExtend);//for this to work in all cases, no require for a widget should be made before this statement executes, above in PageManager, and in modules required in evalUtils (which _WidgetsExtend depends on)
		lang.extend(_FormValueMixin, _WidgetsFormExtend);
		lang.extend(_CheckboxMixin, _WidgetsFormExtend);
		lang.extend(_ComboBoxMenuMixin, _ComboBoxMenuMixinExtend);
	return {
		initializeTukosForm: function(obj){
			tukos = {Pmg: this};
			this.cache = obj;
			this.cache.messages = this.cache.messages || {};
			var self = this;
			has.add("mobileTukos", function(){
				return self.isMobile();
			});
	   },
	   initializeBlog: function(obj){
		   tukos = {Pmg: this};
		   this.cache = obj;
		   this.cache.messages = this.cache.messages || {};
		   this.setObjectsMessages('tabsDescription');
           var self = this;
			has.add("mobileTukos", function(){
				return self.isMobile();
			});
           Date.prototype.toJSON = function(){
               return dojoDateLocale.format(this, {formatLength: "long", selector: "date", datePattern: 'yyyy-MM-dd HH:mm:ss'});
           };
           require([obj.isMobile ? "tukos/mobile/buildBlog" : "tukos/desktop/buildBlog", "tukos/StoresManager"], function(buildBlog, StoresManager){
	           stores = new StoresManager();
	           buildBlog.initialize();
		        self.editorGotoTab = function(target, event){
		            event.stopPropagation();
		        	self.tabs.gotoTab({action: 'Tab', mode: 'Tab', object: 'backoffice', view: 'edit', query: lang.mixin({form: 'Show', object: 'blog'}, target.query)});
		        };
           });
	   },
	   initializeForm: function(obj){
		   tukos = {Pmg: this};
		   this.cache = obj;
		   this.cache.messages = this.cache.messages || {};
		   this.setObjectsMessages('formDescription');
           var self = this;
			has.add("mobileTukos", function(){
				return self.isMobile();
			});
           Date.prototype.toJSON = function(){
               return dojoDateLocale.format(this, {formatLength: "long", selector: "date", datePattern: 'yyyy-MM-dd HH:mm:ss'});
           };
           require([obj.isMobile ? "tukos/mobile/buildForm" : "tukos/desktop/buildForm", "tukos/StoresManager"], function(buildForm, StoresManager){
	           stores = new StoresManager();
	           buildForm.initialize();
           });
	   },
	   initializeNoPage: function(obj){
	   		this.cache= obj;
		   	this.cache.messages = this.cache.messages || {};
			var self = this;
			has.add("mobileTukos", function(){
				return self.isMobile();
			});
	   },
	   initialize: function(obj) {
            tukos = {Pmg: this}; // tukos is a global variable
            this.cache = obj;
            this.cache.extras = this.cache.extras || {};
            this.cache.messages = this.cache.messages || {};
		    this.setObjectsMessages('tabsDescription');
            var self = this;
            if ('serviceWorker' in navigator){
	            navigator.serviceWorker.ready.then(registration => {
					navigator.serviceWorker.controller && navigator.serviceWorker.controller.postMessage({type: 'translations', content: self.messages(self.cache.swToTranslate)});
				});
			}
            Date.prototype.toJSON = function(){
                return dojoDateLocale.format(this, {formatLength: "long", selector: "date", datePattern: 'yyyy-MM-dd HH:mm:ss'});
            };
			has.add("mobileTukos", function(){
				return self.isMobile();
			});
            require([obj.isMobile ? "tukos/mobile/buildPage" : "tukos/desktop/buildPage", "tukos/StoresManager"], function(buildPage, StoresManager){
            	stores = new StoresManager();
            	buildPage.initialize();
		        self.editorGotoTab = function(target, event){
		            event.stopPropagation();
		        	self.tabs.gotoTab(target);
		        };
		        if (!obj.isMobile){
					self.lazyCreateAccordion = buildPage.lazyCreateAccordion;
				}
            });
        },
        serverTranslations: function(expressions, actModel, language){
            var self = this, results = {}, actionModel = actModel || 'GetTranslations', objectsUntranslations = this.cache.objectsUntranslations || (this.cache.objectsUntranslations = {}), objectsMessages = this.cache.objectsMessages || (this.cache.objectsMessages = {});
			if (actionModel === 'GetUntranslations'){
                utils.forEach(expressions, function(objectTranslations, objectName){
					const objectUntranslations = objectsUntranslations[objectName] || {}, foundIndexes = [];
					objectTranslations.forEach(function(translation, index){
						const expression = objectUntranslations[translation];
						if (expression){
							const objectResults = results[objectName] || (results[objectName] = {}); 
							objectResults[translation] = expression;
							foundIndexes.push(index);
						}else{
							const objectMessages = objectsMessages[objectName];
							if(objectMessages){
							  	utils.some(objectMessages, function(cacheTranslation, expression){
									if (cacheTranslation === translation){
										const objectResults = results[objectName] || (results[objectName] = {}); 
										objectResults[translation] = expression; 
										foundIndexes.push(index);
										return true;
									}else{
										return false;
									}
							 	});
							}
						}
					});
					if (foundIndexes.length === objectTranslations.length){
						delete expressions[objectName];
					}else{
						foundIndexes.forEach(function(index){
							objectTranslations.splice(index, 1);								
						});
					}
				});
			}else if(!utils.empty(self.cache.objectsMessages)){
                utils.forEach(expressions, function(objectExpressions, objectName){
					const objectMessages = self.cache.objectsMessages[objectName];
					if (objectMessages){
						const foundIndexes = [];
						objectExpressions.forEach(function(expression, index){
							const translation = objectMessages[expression];
							if (translation){
								const objectResults = results[objectName] || (results[objectName] = {}); 
								objectResults[expression] = translation;
								foundIndexes.push(index);
							}
						});
						if (foundIndexes.length === objectExpressions.length){
							delete expressions[objectName];
						}else{
							foundIndexes.forEach(function(index){
								objectExpressions.splice(index, 1);								
							});
						}
					}
				});
			}
			if (utils.empty(expressions)){
				return results;
			}else{
                const query  = {params: {actionModel: actionModel}};
                if (language){
					query.language = language;
				}
                return self.serverDialog({object: 'tukos', view: 'NoView', action: 'Get', query: query}, {data: expressions}, self.message('actionDone')).then(function (response){
                        utils.forEach(response.data, function(translations, objectName){
                            var objectUntranslations = self.cache.objectsUntranslations[objectName] || (self.cache.objectsUntranslations[objectName] = {}), objectTranslations = self.cache.objectsMessages[objectName] || (self.cache.objectsMessages[objectName] = {});
                            results[objectName] = results[objectName] || {};
                            utils.forEach(translations, function(translation, expression){
                                objectTranslations[expression] = translation;
                                if (actionModel === 'GetTranslations'){
                                    results[objectName][expression] = translation;
                                }else{
                                    results[objectName][translation] = expression;
                                	objectUntranslations[translation] = expression;
                                }
                            });
                        });
                        return results;
                });
			}
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
					if (eventHandle){eventHandle.resume();}
					return response;
				},
				function(response){
					if(dfd){dfd.cancel(true);}
					if (eventHandle){eventHandle.resume();}
					return response;
				}
			);
			return promise;			
		},
		tukosTooltipName: function(name){
			const tooltips = this.cache.presentTukosTooltips || [], userRights = this.cache['userRights'], rightsMap = {SUPERADMIN: 'SuperAdmin', ADMIN: 'Admin', ENDUSER: 'EndUser', RESTRICTEDUSER: 'Restricted'};
			let index = tooltips.indexOf(rightsMap[userRights] + name);
			if (index === -1){
				const rightsKeys = Object.keys(rightsMap);
				let currentRights = rightsKeys.indexOf(userRights) + 1;
				while (currentRights <= rightsKeys.length && index === -1){
					index = tooltips.indexOf(rightsMap[rightsKeys[currentRights]] + name);
					currentRights += 1;
				}
				if (index === -1){
					index = tooltips.indexOf(name);
				}
			}
			return index === -1 ? false : tooltips[index];
		},
		viewTranslatedInBrowserWindow: function(toTranslate, object, language){
			const name = 'tukosItem' + toTranslate + object;
			let screenX = 25, screenY = 50, winPosition;
			if (windows[name]){// if we don't close an existing window, focusing on it will not bring it to the front
				winPosition = windows[name].winPosition;
				if (!windows[name].closed){
					windows[name].close();
				}
			}else{
				winPosition = numWindows;
				numWindows +=1;
			}
			when (this.serverTranslations({[object]: [toTranslate]}, 'GetTranslations', language), function(translation){
        		windows[name] = utils.viewInBrowserWindow(name, translation[object][toTranslate], screenX + 35*winPosition, screenY+25*winPosition);
        		windows[name].winPosition = winPosition;
			});
		},
		viewTranslatedInTooltipWindow: function(domNode, toTranslate, object, language){
			when (this.serverTranslations({[object]: [toTranslate]}, 'GetTranslations', language), function(translation){
				domNode.title = translation[object][toTranslate];
			});
		},
		dataURItoBlob: function(dataURI) {
		  // convert base64 to raw binary data held in a string
		  // doesn't handle URLEncoded DataURIs - see SO answer #6850276 for code that does this
		  var byteString = atob(dataURI.split(',')[1]);
		
		  // separate out the mime component
		  var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0]
		
		  // write the bytes of the string to an ArrayBuffer
		  var ab = new ArrayBuffer(byteString.length);
		
		  // create a view into the buffer
		  var ia = new Uint8Array(ab);
		
		  // set the bytes of the buffer to the correct values
		  for (var i = 0; i < byteString.length; i++) {
		      ia[i] = byteString.charCodeAt(i);
		  }
		
		  // write the ArrayBuffer to a blob, and you're done
		  var blob = new Blob([ab], {type: mimeString});
		  return blob;
		
		},
		viewUrlInBrowserWindow: function(url, name, options){
			if (windows[name] && !windows[name].closed){
				window.blur();
				windows[name].focus();
			}else{
	        	if (url.length > 10 && url.substring(0,10) === 'data:image'){
	        		windows[name] = window.open(URL.createObjectURL(this.dataURItoBlob(url)), name, options);
	        	}else{
	        		windows[name] = window.open(url, name, options);
				}
			}
		},
		closeDependingWindows: function(){
			utils.forEach(windows, function(win){
				if (win && !win.closed){
					win.close();
				}
			});
		},
		setObjectsMessages: function(descriptionType){
			var self = this;
			this.cache[descriptionType].forEach(function(description){
				if (description.messages && description.formContent){
					self.addMessagesToCache(description.messages, description.formContent.object);
				}
			});
		},        
		get: function(item){
            return this.cache[item];
        },
        isRestrictedUser: function(){
			return this.cache.userRights === 'RESTRICTEDUSER';
		},
		isAtLeastAdmin: function(){
			return utils.in_array(this.cache.userRights, ['SUPERADMIN', 'ADMIN']);
		},
        set: function(item, value){
        	this.cache[item] = value;
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
            options = lang.mixin({method: 'POST', timeout: self.getCustom('defaultClientTimeout'), handleAs: 'json'},  options);
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
                        if (defaultFeedback && defaultFeedback[0] === '~'){
							self.addFeedback(response.feedback, defaultFeedback.substring(1), ' ');
						}else{
							 self.addFeedback(response.feedback, defaultFeedback, ' ');
						}
                    }
                    if (isObjectFeedback){
                    	set(att, attValue);
                    }
                    return response;
                },
                function(error){
                    self.setFeedback(self.message('failedOperation'));
                    if (isObjectFeedback){
                    	set(att, attValue);
                    }
					const headerRows = function(rowsDescription){
						let returnedString = '';
						rowsDescription.forEach(function(description){
							returnedString +=  '<p><b>' + self.message(description[0]) +  ':</b> ' + description[1];
						});
						return returnedString;
					};
					request(self.requestUrl({action: 'Process', mode: 'Tab', object: 'tukos',  query: {params: {noget: true, process: 'sendContent'}}, view: 'Edit'}), {handleAs: 'json', method: 'POST', timeout: self.getCustom('defaultClientTimeout'), 
						data: JSON.stringify({fromwhere: {name: 'tukosbackoffice'}, to: 'tukosbackoffice@gmail.com', subject: 'tukos bug - ' + window.location, header: headerRows([['ClientErrormessage', error.message],['PageUrl', window.location], ['userid', self.cache.userid], ['UrlSent', error.response.url], ['Datasent', options.data || self.message('noDataSent')]]),
							   content: '<p><b>' + self.message('Servererrormessage') + '</b><br>' + error.response.text, sendas: 'appendtobody'})}).then(
							function(){
								self.addFeedback(self.message('Supportinformed'));
							},
							function (){
								self.addFeedback(self.message('Contactsupport'));
							}
						);
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
            var newFeedback = (serverFeedback != null && typeof serverFeedback == "object" && serverFeedback.length > 0) ? serverFeedback.join('<br> ... ') : (typeof serverFeedback === 'string' ? serverFeedback  : (clientFeedback || '')), widget,
                  currentTab = this.tabs ? this.tabs.currentPane() : false, self = this;
			if (this.focusedPanel === "leftPanel"){
				var currentPane = this.accordion ? this.accordion.currentPane() : false, form = currentPane.form || {};
				if (form.getWidget){
					widget = form.getWidget('feedback');
				}
			}         
			if (!widget){
				const form = (currentTab || {}).form || {};
				if (form.getWidget){
					widget = form.getWidget('feedback');
				}
			}
            if (widget){
                if (widget.domNode.style.display === 'none'){
					widget.domNode.style.display = 'block';
				}
                if (beep){
					newFeedback = '<div style="color: red; font-weight: 500;">' + newFeedback + '</div>';
				}
                widget.set('value', separator ? widget.get('value') + separator + newFeedback : newFeedback);
				widget.domNode.scrollTop = widget.domNode.scrollHeight;
            }
            if (this.logWidget){
            	this.logWidget.set('value', this.logWidget.get('value') + (separator ? separator : '<br>') + newFeedback);
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
        addFeedback: function(serverFeedback, clientFeedback, separator, beep){
            this.setFeedback(serverFeedback, clientFeedback, separator || "<br>", beep);
        },
		setFeedbackAlert: function(feedback){
			this.setFeedback(feedback, null, null, true);
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
        	return object 
        		? ((this.cache.objectsMessages || {})[object] || {})[key] || this.cache.messages[key] || (key.toLowerCase ? (this.cache.messages[key.toLowerCase()] || key) : key)
        		: this.cache.messages[key] || (key.toLowerCase ? (this.cache.messages[key.toLowerCase()] || key) : key);
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
    		});
    		return store;
    	},
        widgetNameTranslator: function(widgetName, objectName, form){
            return ((this.cache.objectsMessages || {})[objectName] || {})[widgetName] || this._getWidgetNameTranslation(widgetName, objectName, form);
        },
        
        widgetNameUntranslator: function(translatedWidgetName, objectName, form){
            return ((this.cache.objectsUntranslations || {})[objectName] || {})[translatedWidgetName] || this._getWidgetNameUntranslation(translatedWidgetName, objectName, form);
        },

        _getWidgetNameTranslation: function(widgetName, objectName, form){
            var form = form || this.tabs.objectPane(objectName, 'Edit'), objectsUntranslations = this.cache.objectsUntranslations || (this.cache.objectsUntranslations = {}), objectUntranslations = objectsUntranslations[objectName] || (objectsUntranslations[objectName] = {}), 
            	objectsTranslations = this.cache.objectsMessages || (this.cache.objectsMessages = {}), objectTranslations = objectsTranslations[objectName] || (objectsTranslations[objectName] = {}), translation;
            if (form){
                var widget = form.getWidget(widgetName);
                if (widget){
                	translation = widget.label || widget.title || undefined;
                }else{
					translation = this.message(widgetName, objectName);
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
            var form = form || this.tabs.objectPane(objectName, 'Edit'), objectsUntranslations = this.cache.objectsUntranslations || (this.cache.objectsUntranslations = {}), objectUntranslations = objectsUntranslations[objectName] || (objectsUntranslations[objectName] = {}), 
            	objectsTranslations = this.cache.objectsMessages || (this.cache.objectsMessages = {}), objectTranslations = objectsTranslations[objectName] || (objectsTranslations[objectName] = {}), unTranslation;
            if (form){
                form.widgetsName.some(function(widgetName){
                    var widget = form.getWidget(widgetName), translation;
                    if (widget){
                    	var translation = objectTranslations[widgetName] = form.getWidget(widgetName).title || form.getWidget(widgetName).label || undefined;
                    }
                    if (translation){
                        objectUntranslations[translation.toLowerCase()] = widgetName;
	                    if (translation.toLowerCase() === translatedWidgetName.toLowerCase()){
	                        unTranslation = widgetName;
	                        return true;
	                    }
                    }
                });
            }
            return unTranslation;
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
            	return args ? utils.drillDown(this.cache.newPageCustomization, args.split('.'), absentValue) : this.cache.newPageCustomization;
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
        }, 
		idsNamesStore: function(items, capitalize){
			const self = this, result = [];
			items.forEach(function(item){
				const name = self.message(item);
				result.push({id: item, name: capitalize ? (name.charAt(0).toUpperCase() + name.substring(1).toLowerCase()) : name});
			});
			return result;
		}
    };
});