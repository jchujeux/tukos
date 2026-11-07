define (["dojo/_base/declare", "dojo/_base/lang", "dojo/_base/Deferred", "dojo/when", "dojo/promise/all", "dojo/on", "dojo/aspect", "dijit/registry", "tukos/utils",
         "tukos/widgetUtils", "tukos/evalutils", "tukos/PageManager", "dojo/i18n!tukos/nls/messages"], 
    function(declare, lang, Deferred, when, all, on, aspect, registry, utils,  wutils, eutils, Pmg, messages){
    return declare(null, {
        widgetsBeingSet: {},
		setAttrCompleted: function(widgetName){
			var widgetsBeingSet = this.widgetsBeingSet;
			if (widgetsBeingSet[widgetName]){
				var setterDfd = new Deferred(true), watcher;
				watcher = setInterval(function(){
						if (!widgetsBeingSet[widgetName]){
							setterDfd.resolve();
							clearInterval(watcher);
						}
					}, 100);
				return setterDfd;
			}else{
				return true;
			}
		},            
		decorate: function(widget){
            var self = this;
            let menuItemsArgs = [];
            if (widget.afterActions){
				utils.forEach(widget.afterActions, function(action, methodName){
					aspect.after(widget, methodName, lang.hitch(widget, eutils.eval(action, 'args'))/*, true*/);
				});
			}
            if (widget.beforeActions){
				utils.forEach(widget.beforeActions, function(action, methodName){
					aspect.before(widget, methodName, lang.hitch(widget, eutils.eval(action, 'args')));
				});
			}
			if (widget.aroundActions){
				utils.forEach(widget.aroundActions, function(action, methodName){
					aspect.around(widget, methodName, lang.hitch(widget, eutils.eval(action, 'originalName')));
				});
			}
			require(["tukos/menuUtils", "tukos/widgets/widgetCustomUtils"], function(mutils, wcutils){
                if (!Pmg.isRestrictedUser()){
	                menuItemsArgs = lang.hitch(wcutils, wcutils.customizationContextMenuItems)(widget), widgetName = widget.widgetName;
	                if(utils.in_array(widgetName, self.objectIdCols)){
	                    menuItemsArgs = menuItemsArgs.concat(lang.hitch(self, wcutils.idColsContextMenuItems)(widget));
	                }
				}
				if (widget.customContextMenuItems){
					menuItemsArgs = (menuItemsArgs || []).concat(widget.customContextMenuItems());
				}                
				mutils.buildContextMenu(widget,{type: 'DynamicMenu', atts: {targetNodeIds: [widget.domNode]}, items: menuItemsArgs || []});
            });
        },
        getWidget: function(widgetName){
            var widget = registry.byId(this.id + widgetName);
        	return widget;
        },
        valueOf: function(widgetName, undefinedIfNotFound){
            var widget =  registry.byId(this.id + widgetName) || (this.form ? registry.byId(this.form.id + widgetName) : undefined);
            if (widget){
                var result =  widget.get('serverValue') || widget.get('value');
                return (typeof result === 'undefined' || result === null) ? '' : result;
            }else{
        	  	return undefinedIfNotFound ? undefined :  '';
            }
        },
        displayedValueOf: function(widgetName, undefinedIfNotFound){
            var widget =  registry.byId(this.id + widgetName) || (this.form ? registry.byId(this.form.id + widgetName) : undefined);
            if (widget){
                var result =  widget.get('displayedValue') || widget.get('serverValue') || widget.get('value');
                return (typeof result === 'undefined' || result === null) ? '' : result;
            }else{
        	  	return undefinedIfNotFound ? undefined :  '';
            }
        },
        displayedHtmlOf: function(widgetName){
            var widget =  registry.byId(this.id + widgetName) || (this.form ? registry.byId(this.form.id + widgetName) : undefined);
            if (widget){
                return result =  widget.domNode.innerHTML;
            }else{
        	  	return undefinedIfNotFound ? undefined :  '';
            }
        },
        exportAction: function(description){
            return description ? eutils.actionFunction(this, 'export', this[description]) : ('exportfunctiondescriptionnofound' + ': ' + description);
        },
        setValueOf: function(widgetName, value){
            var widget = registry.byId(this.id + widgetName);
            if (widget){
                widget.set('value', value, '');
            }
        },
        setValuesOf: function(values){
			for (var widgetName in values){
				this.setValueOf(widgetName, values[widgetName]);
			}
		},
		emptyWidgets: function(widgetsName){
            var self = this,
                onLoadDeferredWidgets = [];
            widgetsName.forEach(function(widgetName, index){
                if (self.doNotEmpty && utils.in_array(widgetName, self.doNotEmpty)){
                	return;
                }
            	var widget = self.getWidget(widgetName);
                if (widget && widget.get('value')!== ''){
                    if (widget.onLoadDeferred){
                        onLoadDeferredWidgets.push(widget.onLoadDeferred.promise);
                    }
                    widget.set('value', '', false, '');// 4th argument is displayedValue, needed for ObjectSelect (or else a query to the server is made) & ObjectSelectMulti (or else previous displayedValue gets restored)
                }
            });
            if (!utils.empty(onLoadDeferredWidgets)){
                return all(onLoadDeferredWidgets);/*.then(function(){
                    return true;
                });*/
            }else{
                return true;
            }
        },
        setWidgets: function(data){
            var self = this,
                  onLoadDeferredWidgets = [];
            for (var att in data){
                for (var widgetName in data[att]){
                    var widget = this.getWidget(widgetName);
                    if (widget){
                        if (widget.onLoadDeferred){
                            onLoadDeferredWidgets.push(widget.onLoadDeferred.promise);
                        }
                        var newAtt = data[att][widgetName] === null ? '' : data[att][widgetName],
                              oldAtt = widget.get(att),
                              modifiedAtt = utils.mergeRecursive(oldAtt, newAtt);
                        if (utils.wasModified){
                            widget.set(att, modifiedAtt);
                            if (att === 'hidden'){
                                this.mayNeedResize = true;
                            }
                        }
                    }
                }
            }
            if (!utils.empty(onLoadDeferredWidgets)){
                return all(onLoadDeferredWidgets);/*.then(function(){
                    return true;
                });*/
            }else{
                return true;
            }
       },
		setChangedWidget: function(widget, context){
			if (!widget.ignoreChanges){
				var name = widget.widgetName;
				this.changedWidgets[name] = widget;
	            if ((context || this.watchContext) === 'user'){
	            	this.userChangedWidgets[name] = widget;
	            }
			}
		},
		setUnchangedWidget: function(widget){
			delete(this.changedWidgets[widget.widgetName]); 
			delete(this.userChangedWidgets[widget.widgetName]); 
		},
		hasChanged: function(widgetName){
           return widgetName ? this.changedWidgets[widgetName] : !utils.empty(this.changedWidgets);
       },
		changesCount: function(){
			return utils.count(this.changedWidgets);
		},
		userChangesCount: function(){
			return utils.count(this.userChangedWidgets);
		},
       userHasChanged: function(ignoreWidgets){
    	   var hasChanged = {}, postElts = this.get('postElts');
    	   if (utils.some(this.userChangedWidgets, function(widget, widgetName){
					return utils.in_array(widgetName, postElts) && !utils.in_array(widgetName, ignoreWidgets);
				})){
				hasChanged.widgets = true;
    	   }
    	   if (!utils.empty(this.customization) && (Pmg.getCustom('ignoreCustomOnClose') !== 'YES')){
    		   hasChanged.customization = true;
    	   }
    	   return utils.empty(hasChanged) ? false : hasChanged;
       },
       checkChangesDialog: function(action, ignoreCustom, ignoreWidgets, timeout){
           var changes = this.userHasChanged(ignoreWidgets);
       	if (!changes || (ignoreCustom && !changes.widgets)){
               return action();
           }else{
               return Pmg.confirmForgetChanges(changes).then(
                   function(){return timeout === undefined ? action() : setTimeout(action, timeout)},
                   function(){Pmg.setFeedback(Pmg.message('actionCancelled'));}
               );
           }
       },
        resetChangedWidgets: function(){
            var changedWidgets = this.changedWidgets;
            for (var widgetName in changedWidgets){
                wutils.setStyleToUnchanged(changedWidgets[widgetName]);
            }
            this.changedWidgets = {};
            this.userChangedWidgets = {};
        },
        widgetsValue: function(widgetsName){
            var widgetsValue = {};
            widgetsName.forEach(lang.hitch(this, function(widgetName){
            	widgetsValue[widgetName] = this.valueOf(widgetName);
            }));
            return widgetsValue;
        },
        changedValues: function(widgetsName){
            if (widgetsName == undefined){
                widgetsName = this.get('postElts');
            }
            var changedValues = {};
            widgetsName.forEach(lang.hitch(this, function(widgetName){
            	var widget = this.changedWidgets[widgetName];
            	if (widget){
            		changedValues[widgetName] = widget.get('serverValue') || widget.get('value');// Warning: to check related to MultiSelectObject
            	}
            }));
            return changedValues;
        },
        keepChanges: function(options){
            if (options){
                let widgetChanges = false;
                if (options.values && !utils.empty(this.changedWidgets)){
                    widgetChanges = {};
                    for (var widgetName in this.changedWidgets){
                            var widget = this.changedWidgets[widgetName];
                            widgetChanges[widgetName] =  typeof widget.keepChanges === 'function' ? widget.keepChanges() : widget.get('value');
                    }
                }
                if (options.customization && this.customization) {
                    return {widgets: widgetChanges, customization: this.customization};
                }else{
                    return {widgets: widgetChanges};
                }
            }
        },
        restoreChanges: function(changes, options){
            if (options){
                if (options.values){
                    var widgetChanges = changes.widgets;
                    for (var widgetName in widgetChanges){
                        var widget = this.getWidget(widgetName);
                        if (typeof widget.restoreChanges === 'function'){
                            widget.restoreChanges(widgetChanges[widgetName]);
                        }else{
                            widget.set('value', widgetChanges[widgetName]);
                        }
                    }
                }
                if (options.customization && changes.customization){
                    this.customization = changes.customization;
                }
            }
        },
        widgetWatchLocalAction: function(widget, watchedAtt, localActionFunctions, newValue, oldValue){
            var allowedNestedWatchActions = this.allowedNestedWatchActions, nestedWidgetWatchActions = widget.nestedWatchActions = widget.nestedWatchActions || {},
            	self = this;
            this.nestedWatchActions = this.nestedWatchActions || 0;
            nestedWidgetWatchActions[watchedAtt] = nestedWidgetWatchActions[watchedAtt] || 0;
            for (var widgetName in localActionFunctions){
                var targetWidget = this.getWidget(widgetName), widgetActionFunctions =  localActionFunctions[widgetName];
                if (targetWidget){
	                for (var att in widgetActionFunctions){
	                    if (allowedNestedWatchActions === undefined || (this.nestedWatchActions <= allowedNestedWatchActions) && nestedWidgetWatchActions[watchedAtt] < 1 && ((targetWidget.nestedWatchActions || {})[att] || 0) < 1){
	                        var actionFunction = widgetActionFunctions[att];
	                        if (actionFunction.triggers[this.watchContext]){
	                    		this.nestedWatchActions += 1;
	                        	nestedWidgetWatchActions[watchedAtt] += 1;
	                            var newAtt = actionFunction.action(widget, targetWidget, newValue, oldValue);
	                            if (newAtt != undefined && widget){
	                                if (targetWidget && newAtt !== targetWidget.get(att)){
	                                    targetWidget.set(att, newAtt);
	                                    if (att === 'hidden'){
	                                        this.mayNeedResize = true;
	                                    }
	                                }
	                            }
	                    		this.nestedWatchActions += -1;
	                        	nestedWidgetWatchActions[watchedAtt]  += -1;
	                        }
	                    }
	                }
				}
            }
        },
        buildLocalActionFunctions: function(localActionFunctions, actionDescriptions){
            for (var widgetName in actionDescriptions){
                localActionFunctions[widgetName] = {};
                var description = actionDescriptions[widgetName];
                for (var att in description){
                    localActionFunctions[widgetName][att] = {};
                    var attDescription = description[att];
                    localActionFunctions[widgetName][att].triggers = attDescription.triggers ? attDescription.triggers : {server: false, user: true};
                    var action = (attDescription.action ? attDescription.action : attDescription);
                    localActionFunctions [widgetName][att].action = (typeof action === 'string') ? eutils.eval(action, 'sWidget, tWidget, newValue, oldValue') : action;
                }
            }
        },
        
        buildSubWidgetLocalActionFunction: function(action){
        	return eutils.eval(action, 'widget, oldValue, newValue, oldValue')
        },
        serverAction: function(urlArgs, options){
            var self = this, widgetsName, requestOptions= {};
            if (options){
                 requestOptions = lang.clone(options);
                if (requestOptions.includeWidgets){
                    widgetsName = requestOptions.includeWidgets;
                    delete requestOptions.includeWidgets;
                }else{
                     widgetsName = this.widgetsName;
                }
                if (requestOptions.excludeWidgets){
                    widgetsName = utils.array_diff(widgetsName, requestOptions.excludeWidgets);
                    delete requestOptions.excludeWidgets;
                }
            }else{
                widgetsName = this.widgetsName;
            }
            if (requestOptions.includeFormWidgets){
            	requestOptions.data = {values: this.form.widgetsValue(requestOptions.includeFormWidgets), atts: this.widgetsValue(widgetsName)}
            }else{
                requestOptions.data = this.widgetsValue(widgetsName);
            }
            Pmg.setFeedback(messages.actionDoing);
            return Pmg.serverDialog(this.completeUrlArgs(urlArgs), requestOptions, messages.actionDone).then(
                function(response){
                    self.watchContext = 'server';
                    return when(self.setWidgets(response['data']), function(){
                        self.watchContext = 'user';
                        return response;
                    });
                }
            ); 
        },
        completeUrlArgs: function(urlArgs){
            var form = this.form || this;
            urlArgs.object = urlArgs.object || form.object;
            urlArgs.view = urlArgs.view || form.viewMode || 'NoView';
            urlArgs.mode = urlArgs.mode || form.paneMode || 'NoMode';
            if (urlArgs.query && urlArgs.query.id && urlArgs.query.id === true){
                urlArgs.query.id = registry.byId(form.id + 'id').get('value');
            }
            return urlArgs;
        },
        openAction: function(description){
            return description ? (typeof description === 'string' ? eutils.actionFunction(this, 'open', description) : lang.hitch(this, description)()) : '';
        }
    });
});
