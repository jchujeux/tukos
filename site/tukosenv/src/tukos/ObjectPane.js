define (["dojo/_base/declare",  "dojo/_base/lang", "dojo/when", "dojo/dom-construct",  "dijit/layout/ContentPane", "dijit/layout/BorderContainer", "dijit/registry", "tukos/utils",  "tukos/evalutils", "tukos/widgetUtils", "tukos/_TukosLayoutMixin", "tukos/_ObjectPaneMixin",
		 "tukos/PageManager"], 
    function(declare, lang, when, dct, ContentPane, BorderContainer, registry, utils, eutils, wutils, _TukosLayoutMixin,  _ObjectPaneMixin, Pmg){
    return declare([BorderContainer, _TukosLayoutMixin, _ObjectPaneMixin], {

        postCreate: function(){
			var self = this;            
			this.inherited(arguments);
            this.widgetType = "ObjectPane";
			this.Pmg = Pmg;
            this.widgetsName = [];
            this.customization = {};
            if (this.beforeInstantiationAction){
				eutils.actionFunction(this, 'beforeInstantiation', this.beforeInstantiationAction);
			}
            var dataPane = new ContentPane({region: "center", 'class': "centerPanel", style: "padding: 0px;  overflow: auto; width: 100%; height: 100%; "}, dojo.doc.createElement("div"));
            var dataTable = this.tableLayout(this.dataLayout, dataPane, lang.hitch(wutils, wutils.setWatchers), this.commonWidgetsAtts);
            this.addChild(dataPane);
            if (!utils.empty(this.actionLayout)){
	            var actionPane = new ContentPane({region: "top", 'class': "edgePanel", style: "padding: 0px; overflow: auto;"},  dojo.doc.createElement("div"));
	            var actionTable = this.tableLayout(this.actionLayout, actionPane, lang.hitch(wutils, wutils.setWatchers));
	            this.addChild(actionPane);
            }
            if (!utils.empty(this.summaryLayout)){
                var summaryPane = new ContentPane({region: "bottom", 'class': "edgePanel", style: "padding: 0px; overflow: auto;"},  dojo.doc.createElement("div"));
                var summaryTable = this.tableLayout(this.summaryLayout, summaryPane);
                this.addChild(summaryPane);
            }
            this.changedWidgets = {};
            this.userChangedWidgets = {};
            if (this.forceMarkIfChanged){
                this.markIfChanged = true;
                this.forceMarkIfChanged = false;
            }else{
                this.markIfChanged = false;
            }
            this.watchOnChange = true;
            this.watchContext = 'server';
            this.onInstantiated(lang.hitch(this, function(){
                if (actionPane && this.widgetsHider !== false && !Pmg.isRestrictedUser()){
            		this.widgetsHiderButton = dct.create('button', {'class': 'ui-icon dgrid-hider-toggle', type: 'button'}, actionPane.domNode);
                	this.widgetsHiderButton.onclick = function(){
                		var widgetsHiderButton = self.widgetsHiderButton, hider = widgetsHiderButton.hider;
                		if(!hider){
                    		require(["tukos/_WidgetsHider"], function(_WidgetsHider){
                    			(hider = widgetsHiderButton.hider = new _WidgetsHider({form: self, buttonNode: widgetsHiderButton})).toggleHiderMenu();
                    		});
                		}else{
                			hider.toggleHiderMenu();
                		}
                	};
                }
                if (this.data && this.data.value && !this.data.value.id){
                    this.markIfChanged = true;
                }
                dojo.ready(lang.hitch(this, function(){
					when (this.setWidgets(this.data), lang.hitch(this, function(result){
	                    if (this.onOpenAction){
	                        this.openAction(this.onOpenAction);
	                    }
	                    setTimeout(lang.hitch(this, function(){// needed due to a setTimeout in _WidgetBase.defer causing problem of markIfChanged being true in the onCHange event of SliderSelect (at least)
							if (this.hasOwnProperty('openActionCompleted')){
								const form = this;
								utils.waitUntil(
									function(){
										return form.openActionCompleted;
									}, 
									function(){
				                    	form.markIfChanged = true;
				                        form.watchContext = 'user';
				                        form.setUserContextPaths(); 
				                        if (form.offlineChangedValues){
											form.setWidgets({value: form.offlineChangedValues});
										}
										Pmg.setFeedback(Pmg.message('actionDone'), null, ' ');
									}, 
									100);
							}else{
		                    	this.markIfChanged = true;
		                        this.watchContext = 'user';
		                        this.setUserContextPaths(); 
		                        if (this.offlineChangedValues){
									this.setWidgets({value: this.offlineChangedValues});
								}
								Pmg.setFeedback(Pmg.message('actionDone'), null, ' ');
							}
	                    }), 0);
						/*this.needsToFreezeWidth = true;
						this.resize();
						this.needsToFreezeWidth = false;*/
	                }));
				}));
            }));
        },
        setUserContextPaths: function(){
            var userContextWidget = registry.byId('tukos_userContextcontextid');
            if (userContextWidget){
                userContextWidget.set('paths',  this.contextPaths);
            }
        },
        onClose: function(){
            var tab = this.parent, closeAction = function(){
    			tab.getParent().removeChild(tab);
    			tab.destroyRecursive();
            	
            };
            this.checkChangesDialog(closeAction);
            return false;
        }
    }); 
});
