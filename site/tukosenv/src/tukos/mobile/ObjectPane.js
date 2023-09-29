define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-style", "dojo/when", "dojo/aspect", "dijit/registry", "dojox/mobile/Container", 
		"dojox/mobile/ToolBarButton", "tukos/widgets/WidgetsLoader", "tukos/_ObjectPaneMixin", "tukos/_TukosLayoutMixin", "tukos/utils", "tukos/widgetUtils", "tukos/PageManager"], 
    function(declare, lang, dst, when, aspect, registry, Container, ToolBarButton, widgetsLoader, _ObjectPaneMixin, _TukosLayoutMixin, utils, wutils, Pmg){
	return declare([Container, _ObjectPaneMixin, _TukosLayoutMixin], {
        postCreate: function(){
            var self = this;
        	this.inherited(arguments);
        	this.Pmg = Pmg;
            this.widgetType = "MobileObjectPane";
            this.widgetsName = [];
            this.customization = {};
            this.widgets = [];
            this.instantiatingWidgets = {};
            this.tableLayout(this.dataLayout, this, lang.hitch(wutils, wutils.setWatchers), this.commonWidgetsAtts);
            this.layoutAction(this.actionLayout);
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
            	if (this.widgetsHider !== false && !Pmg.isRestrictedUser()){
                	this.widgetsHiderButton = new ToolBarButton({icon: "mblDomButtonBlueCirclePlus", style: "float: right", form: this}).placeAt(this.viewPane.actionsHeading, 'first');
                	this.widgetsHiderButton.on('click', function(evt){
                		var widgetsHiderButton = self.widgetsHiderButton, hider = widgetsHiderButton.hider;
                		if(!hider){
                    		require(["tukos/_WidgetsHider"], function(_WidgetsHider){
                    			(hider = widgetsHiderButton.hider = new _WidgetsHider({form: widgetsHiderButton.form, buttonNode: widgetsHiderButton.domNode, button: widgetsHiderButton})).toggleHiderMenu();
                    			widgetsHiderButton.set('icon', "mblDomButtonBlueCircleMinus");
                    			aspect.before(self.viewPane.mobileViews, 'selectPane', function(method, args){
                    				hider.close();
                    			});
                    		});
                		}else{
                			hider.toggleHiderMenu();
                		}
                	});
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
										Pmg.setFeedback(Pmg.message('actionDone'));
									}, 
									100);
							}else{
		                    	this.markIfChanged = true;
		                        this.watchContext = 'user';
		                        this.setUserContextPaths(); 
		                        if (this.offlineChangedValues){
									this.setWidgets({value: this.offlineChangedValues});
								}
							}
	                    }), 0);
						this.needsToFreezeWidth = true;
						this.resize();
						this.needsToFreezeWidth = false;
	                }));
				}));
/*                dojo.ready(lang.hitch(this, function(){
					this.resize();//or else spinwheelSlot get('value') gets screwed-up
					when (this.setWidgets(this.data), lang.hitch(this, function(result){
	                    if (this.onOpenAction){
	                        this.openAction(this.onOpenAction);
	                    }
	                    setTimeout(lang.hitch(this, function(){// needed due to a setTimeout in _WidgetBase.defer causing problem of markIfChanged being true in the onCHange event of SliderSelect (at least)
	                    		dojo.ready(function(){
									this.markIfChanged = true;
	                            	this.watchContext = 'user';
	                            	this.setUserContextPaths();
	                            });
	                    	}), 0);
	                }));
	
				}));*/
            }));
        },
        layoutAction: function(layout, optionalWidgetInstantiationCallback){
            var self = this, tableAtts = layout.tableAtts, heading = this.viewPane.heading, actionsHeading = this.viewPane.actionsHeading;
            if (tableAtts && layout.widgets){
            	layout.widgets.forEach(lang.hitch(this, function(widgetName){
                    var widgetDescription = this.widgetsDescription[widgetName], instantiatingWidget, widgetType;
                	if (widgetName !== 'feedback' && widgetDescription && (widgetType = widgetDescription['type'])){
    	                self.widgetsName.push(widgetName);
                		dojo.when(instantiatingWidget = widgetsLoader.instantiate(widgetType, utils.mergeRecursive({id: this.id + widgetName, style: {backgroundColor: 'DarkGrey', paddingLeft: 0, paddingRight: 0, fontSize: '12px'}, pane: this,
                			form: this, widgetType: widgetType, widgetName: widgetName}, widgetDescription['atts']), optionalWidgetInstantiationCallback), function(theWidget){
                				(widgetName === 'logo' ? heading : actionsHeading).addChild(theWidget);
                				theWidget.layoutContainer = theWidget.domNode;
                				if (theWidget.get('hidden')){
                					dst.set(theWidget.domNode, 'display', 'none');
                				}
                				self.decorate(theWidget);
                    	});
                        if (typeof instantiatingWidget.then === "function"){
                        	this.instantiatingWidgets[widgetName] = instantiatingWidget;
                        }
                	}
            	}));
            }
            for (var item in layout.contents){
                this.layoutAction(layout.contents[item], optionalWidgetInstantiationCallback);
            }
        },
        setUserContextPaths: function(){
            var userContextWidget = registry.byId('tukos_userContextcontextid');
            if (userContextWidget){
                userContextWidget.set('paths',  this.contextPaths);
            }
        }
    });
});
