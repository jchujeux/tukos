/*
 *  ObjectPane mixin for dynamic widget information handling (widgets values and attributes that may be modified by the user or the server)
 *   - usage: 
 */
define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-class", "dojo/when", "dojo/promise/all", "dojo/ready", "dijit/registry", 
            "tukos/utils", "tukos/_TukosPaneMixin",
            "tukos/PageManager", "dojo/json", "dojo/i18n!tukos/nls/messages"], 
    function(declare, lang, dcl, when, all, ready, registry, utils, _TukosPaneMixin, Pmg, JSON, messages){
    return declare(_TukosPaneMixin, {

        editInNewTab: function(widget){
            var value = this.valueOf(widget['widgetName']);
            Pmg.tabs.gotoTab({object: (Pmg.objectName(value) || this.object), view: 'Edit', mode: Pmg.isMobile() ? 'Mobile' : 'Tab', action: 'Tab', query: {id: value}});
        },
        showInNavigator: function(widget){
        	Pmg.showInNavigator(this.valueOf(widget['widgetName']));
        },
           
        changedValues: function(widgetsName){
            var changedValues = this.inherited(arguments), valueOf = lang.hitch(this, this.valueOf);
            if (!utils.empty(changedValues)/* && valueOf('id') != ''*/){
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
            var noLoadingIcon = this.noLoadingIcon;
            //Pmg.setFeedback(''/*messages.actionDoing*/);
            if (this.inServerDialog){
            	Pmg.setFeedback(Pmg.message('actionnotcompletedwait'), '', '', true);
            	return false;//should be a deferred/promise
            }else{
                this.inServerDialog = true;
            	urlArgs.object = urlArgs.object || this.object;
                urlArgs.view = urlArgs.view || this.viewMode;
                urlArgs.mode = urlArgs.mode || this.paneMode;
                urlArgs.query = utils.mergeRecursive(urlArgs.query, {contextpathid: this.tabContextId(), timezoneOffset: (new Date()).getTimezoneOffset()});
                return all(data).then(lang.hitch(this, function(data){
                    return Pmg.serverDialog(urlArgs, {data: data}, noLoadingIcon ? defaultDoneMessage : {widget: this.parent, att: 'title', defaultMessage: defaultDoneMessage}).then(lang.hitch(this, function(response){
	                    	if (response['data'] === false){
	    	                        this.inServerDialog = false;
	    	                		return response;
		                    }else if(response['data'] !== undefined){
		                        this.markIfChanged = this.watchOnChange = false;
		                        this.watchContext = 'server';
		                        return when(this.emptyWidgets(emptyBeforeSet), lang.hitch(this, function(){;
		                            this.watchOnChange = true;
		                            this.markIfChanged = (markResponseIfChanged  ? true : false);
		                            return when(this.setWidgets(response['data']), lang.hitch(this, function(){
		                                if (response['title'] && dcl.contains(this.domNode.parentNode, 'dijitTabPane')){
		                                    Pmg.tabs.setCurrentTabTitle(response['title']);
		                                }
		                                setTimeout(lang.hitch(this, function(){// needed due to a setTimeout in _WidgetBase.defer causing problem of markIfChanged being true in the onCHange event of SliderSelect (at least)
		                                	if (!this.markIfChanged){
	    	                                    this.resetChangedWidgets();
		                                	}
		                                	this.markIfChanged = true;
		                                	this.watchContext = 'user';
		                                }, 0));
		                                ready(function(){
		                                    if (Pmg.tabs){
			                                	Pmg.tabs.currentPane().resize();	                                    	
		                                    }
		                                });
		                                this.inServerDialog = false;
		                                return response;
		                            }));
		                        }));
		                    }else{
		                    	this.resetChangedWidgets();
		                        this.inServerDialog = false;
		                    	return response;
		                    }
	    	                }),
    	                lang.hitch(this, function(error){
                    		this.inServerDialog = false;
    	                })
                    );
                }));
            }
        }, 
        tabContextId: function(){
            return this.contextPaths[0][this.contextPaths[0].length -1]['id'];
        }
    });
});
