define (["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "dojo/promise/all", "dojo/on", "dijit/registry", "tukos/utils",
         "tukos/widgetUtils", "tukos/evalutils", "tukos/PageManager", "dojo/i18n!tukos/nls/messages"], 
    function(declare, lang, when, all, on, registry, utils,  wutils, eutils, Pmg, messages){
    return declare(null, {
        decorate: function(widget){
            var self = this;
        	require(["tukos/menuUtils", "tukos/widgets/widgetCustomUtils"], function(mutils, wcutils){
                menuItemsArgs = lang.hitch(wcutils, wcutils.customizationContextMenuItems)(widget), widgetName = widget.widgetName;
                menuItemsArgs = (widgetName === 'id' || utils.in_array(widgetName, self.objectIdCols))
                    ? menuItemsArgs.concat(lang.hitch(self, wcutils.idColsContextMenuItems)(widget))
                    : menuItemsArgs;
                mutils.buildContextMenu(widget,{type: 'DynamicMenu', atts: {targetNodeIds: [widget.domNode]}, items: menuItemsArgs});
            });
        },
        getWidget: function(widgetName){
            var widget = registry.byId(this.id + widgetName);
        	if (!widget){
        		console.log('undefined widget: ' + widgetName);
        	}
        	return widget;
        },
        valueOf: function(widgetName, undefinedIfNotFound){
            var widget =  registry.byId(this.id + widgetName) || (this.form ? registry.byId(this.form.id + widgetName) : undefined);
            if (widget){
                var result =  widget.get('serverValue') || widget.get('value');
                return (typeof result === 'undefined' || result === null) ? '' : result;
            }else{
        	  	console.log('widget: ' + widgetName + ' not found');
        	  	return undefinedIfNotFound ? undefined :  '';
            }
        },
        displayedValueOf: function(widgetName, undefinedIfNotFound){
            var widget =  registry.byId(this.id + widgetName) || (this.form ? registry.byId(this.form.id + widgetName) : undefined);
            if (widget){
                var result =  widget.get('displayedValue') || widget.get('serverValue') || widget.get('value');
                return (typeof result === 'undefined' || result === null) ? '' : result;
            }else{
        	  	console.log('widget: ' + widgetName + ' not found');
        	  	return undefinedIfNotFound ? undefined :  '';
            }
        },
        setValueOf: function(widgetName, value){
            var widget = registry.byId(this.id + widgetName);
            if (widget && !widget.inWatchCallback){
                widget.set('value', value, '');
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
                        onLoadDeferredWidgets.push(widget.onLoadDeferred);
                    }
                    widget.set('value', '', false, '');// 4th argument is displayedValue, needed for ObjectSelect (or else a query to the server is made) & ObjectSelectMulti (or else previous displayedValue gets restored)
                }
            });
            if (!utils.empty(onLoadDeferredWidgets)){
                return all(onLoadDeferredWidgets, function(){
                    return true;
                });
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
                            onLoadDeferredWidgets.push(widget.onLoadDeferred);
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
                return all(onLoadDeferredWidgets, function(){
                    return true;
                });
            }else{
                return true;
            }
       },
       hasChanged: function(widgetName){
           return widgetName ? this.changedWidgets[widgetName] : !utils.empty(this.changedWidgets);
       },
       userHasChanged: function(widgetName){
           return widgetName ? this.userChangedWidgets[widgetName] : !utils.empty(this.userChangedWidgets);
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
                if (options.values){
                    var widgetChanges = {};
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
        widgetWatchLocalAction: function(widget, localActionFunctions, newValue){
            if (!this.inWidgetWatchLocalAction){
                var self = this;
                this.inWidgetWatchLocalAction = true;              
                for (var widgetName in localActionFunctions){
                    var targetWidget = this.getWidget(widgetName),
                          widgetActionFunctions =  localActionFunctions[widgetName];
                    for (var att in widgetActionFunctions){
                        var actionFunction = widgetActionFunctions[att];
                        if (actionFunction.triggers[this.watchContext]){
                            var newAtt = actionFunction.action(widget, targetWidget, newValue);
                            if (newAtt != undefined && widget){
                                if (newAtt !== targetWidget.get(att)){
                                    targetWidget.set(att, newAtt);
                                    if (att === 'hidden'){
                                        this.mayNeedResize = true;
                                    }
                                }
                            }
                        }
                    }
                }
                this.inWidgetWatchLocalAction = false;
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
                    localActionFunctions [widgetName][att].action = (typeof action === 'string') ? eutils.eval(action, 'sWidget, tWidget, newValue') : action;
                }
            }
        },
        
        buildSubWidgetLocalActionFunction: function(action){
        	return eutils.eval(action, 'widget, oldValue, newValue')
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
            if (description){
                if (!this.openActionFunction){
                    var myEval = lang.hitch(this, eutils.eval);
                    this.openActionFunction = myEval(description, '');
                }
                return this.openActionFunction();
            }
            return true;
        }
    });
});
