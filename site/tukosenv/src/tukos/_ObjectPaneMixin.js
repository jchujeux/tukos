/*
 *  ObjectPane mixin for dynamic widget information handling (widgets values and attributes that may be modified by the user or the server)
 *   - usage: 
 */
define (["dojo/_base/array", "dojo/_base/declare", "dojo/_base/lang", "dojo/when", "dojo/promise/all", "dojo/ready", "dijit/registry", "dojo/request", 
            "tukos/utils", "tukos/dateutils", "tukos/hiutils", "tukos/widgetUtils", "tukos/widgets/widgetCustomUtils", "tukos/_WidgetsMixin", "tukos/_TukosPaneMixin",
            "tukos/DialogConfirm", "tukos/PageManager", "dojo/json", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(arrayUtil, declare, lang, when, all, ready, registry, request, utils, dutils, hiutils, wutils, wcutils, _WidgetsMixin, _TukosPaneMixin, DialogConfirm, Pmg, JSON, messages){
    return declare(_TukosPaneMixin, {

        editInNewTab: function(widget){
            var value = this.valueOf(widget['widgetName']);
            Pmg.tabs.request({object: (Pmg.objectName(value) || this.object), view: 'edit', mode: 'tab', action: 'tab', query: {id: value}});
        },
        showInNavigator: function(widget){
        	Pmg.showInNavigator(this.valueOf(widget['widgetName']));
        },
           
        changedValues: function(widgetsName){
            var changedValues = this.inherited(arguments), valueOf = lang.hitch(this, this.valueOf);
            if (!utils.empty(changedValues) && valueOf('id') != ''){
                this.sendOnSave.forEach(function(widgetName){
                	var value = valueOf(widgetName);
                	if (value){
                		changedValues[widgetName] = value;
                	}
                });
            }
            return changedValues;
        },

        serverDialog: function(urlArgs, data, emptyBeforeSet, defaultDoneMessage, markResponseIfChanged){
            var self = this, parent = this.parent, title = parent.get('title');
            Pmg.setFeedback(messages.actionDoing);
            urlArgs.object = urlArgs.object || this.object;
            urlArgs.view = urlArgs.view || this.viewMode;
            urlArgs.mode = urlArgs.mode || this.paneMode;
            urlArgs.query = utils.mergeRecursive(urlArgs.query, {contextpathid: this.tabContextId(), timezoneOffset: (new Date()).getTimezoneOffset()});
            parent.set('title', Pmg.loading(title, true));
            return all(data).then(lang.hitch(this, function(data){
                return Pmg.serverDialog(urlArgs, {data: data}, defaultDoneMessage).then(lang.hitch(this, function(response){
                //return Pmg.serverDialog(lang.hitch(self, self.completeUrlArgs(urlArgs)), {data: data}, defaultDoneMessage).then(lang.hitch(this, function(response){
	                    if (response['data'] === false){
	                        //Pmg.appendFeedback(messages.failedOperation);
	                        parent.set('title', title);
	                    }else if(response['data'] !== undefined){
	                        this.markIfChanged = self.watchOnChange = false;
	                        this.watchContext = 'server';
	                        return when(this.emptyWidgets(emptyBeforeSet), lang.hitch(this, function(){;
	                            this.watchOnChange = true;
	                            this.markIfChanged = (markResponseIfChanged  ? true : false);
	                            return when(self.setWidgets(response['data']), lang.hitch(this, function(){
	                                if (response['title'] && hiutils.hasClass(this.domNode.parentNode, 'dijitTabPane')){
	                                    Pmg.tabs.setCurrentTabTitle(response['title']);
	                                }else{
	                                	parent.set('title', title);
	                                }
	                                if (!this.markIfChanged){
	                                    this.resetChangedWidgets();
	                                }
	                                this.markIfChanged = true;
	                                this.watchContext = 'user';
	                                ready(function(){
	                                    Pmg.tabs.currentPane().resize();
	                                });
	                                return response;
	                            }));
	                        }));
	                    }else{
	                        parent.set('title', title);
	                    	this.resetChangedWidgets();
	                        return response;
	                    }
	                }),
	                function (error){
	                	parent.set('title', title);
	                }
                );
            }));
        }, 
        widgetChangeServerDialog: function(widget){
            var valuesToPost = {'input': {}};
            for (var i in widget.onChangeServerAction.inputWidgets){
                var theWidgetName = widget.onChangeServerAction.inputWidgets[i];
                valuesToPost.input[theWidgetName] = widget.valueOf('#' + theWidgetName);
            }
            if (widget.onChangeServerAction.outputWidgets){
                valuesToPost.output = widget.onChangeServerAction.outputWidgets;
            }

            var url = {action: 'hasChanged', query:{widget: widget.widgetName}};
            if (widget.onChangeServerAction.urlArgs){
                url = utils.mergeRecursive(url, widget.onChangeServerAction.urlArgs);
            }
            this.serverDialog(url, valuesToPost, [], 'action done' /*messages.actionDone*/, true); 
        },

        widgetWatchServerAction: function(widget, actionDescriptions, newValue){
            var url = {action:  'process', query: {widget: widget.widgetName}};
            if (actionDescriptions.urlArgs){
                url = utils.mergeRecursive(url, actionDescriptions.urlArgs);
            }
            this.serverDialog(url, this.changedValues(), [], messages.actionDone, true); 
        },

        checkChangesDialog: function(action){
            var self = this;
            if (!this.hasChanged()){
                return action();
            }else{
                return (new DialogConfirm({title: messages.fieldsHaveBeenModified, content: messages.sureWantToForget, hasSkipCheckBox: false})).show().then(
                    function(){return action()},
                    function(){Pmg.setFeedback(messages.actionCancelled);}
                );
            }
        },
        tabContextId: function(){
            return this.contextPaths[0][this.contextPaths[0].length -1]['id'];
        }
    });
});
