define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dojo/when", "dojo/promise/all", "dojo/aspect", "dijit/registry", "dojox/mobile/ScrollablePane", "dojox/mobile/Container", "dojox/mobile/FormLayout", 
		"dojox/mobile/ToolBarButton", "tukos/widgets/WidgetsLoader", "tukos/_ObjectPaneMixin", "tukos/utils", "tukos/widgetUtils"], 
    function(declare, lang, dct, dst, when, all, aspect, registry, ScrollablePane, Container, FormLayout, ToolBarButton, widgetsLoader, _ObjectPaneMixin, utils, wutils){
    
	var mobileWidgetTypes = {TextBox: 'MobileTextBox', FormattedTextBox: 'MobileFormattedTextBox', LazyEditor: 'MobileEditor', ObjectReset: 'MobileObjectReset', ObjectSave: 'MobileObjectAction', ObjectNew: 'MobileObjectAction',
							 OverviewDgrid: 'MobileOverviewGrid', OverviewAction: 'MobileOverviewAction', Textarea: 'MobileTextBox', StoreSelect: "MobileSliderSelect", TimeTextBox: "MobileTimePicker", TukosNumberBox: "MobileNumberBox"};
	return declare([Container, _ObjectPaneMixin], {
        postCreate: function(){
            var self = this;
        	this.inherited(arguments);
            this.widgetType = "MobileObjectPane";
            this.widgetsName = [];
            this.widgets = [];
            this.instantiatingWidgets = {};
            this.layout(this.dataLayout, lang.hitch(wutils, wutils.setWatchers));
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
            	if (this.widgetsHider !== false){
                	this.widgetsHiderButton = new ToolBarButton({icon: "mblDomButtonBlueCirclePlus", style: "float: right", form: this}).placeAt(this.viewPane.actionsHeading, 'first');
                	this.widgetsHiderButton.on('click', function(evt){
                		var widgetsHiderButton = self.widgetsHiderButton, hider = widgetsHiderButton.hider;
                		if(!hider){
                    		require(["tukos/_WidgetsHider"], function(_WidgetsHider){
                    			(hider = widgetsHiderButton.hider = new _WidgetsHider({form: widgetsHiderButton.form, parent: widgetsHiderButton})).toggleHiderMenu();
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
                when (this.setWidgets(this.data), lang.hitch(this, function(result){
                    if (this.onOpenAction){
                        this.openAction(this.onOpenAction);
                    }
                    setTimeout(lang.hitch(this, function(){// needed due to a setTimeout in _WidgetBase.defer causing problem of markIfChanged being true in the onCHange event of SliderSelect (at least)
                    		this.markIfChanged = true;
                            this.watchContext = 'user';
                            this.setUserContextPaths(); 
                    	}), 0);
                }));
            }));
        },
        layout:    function(layout, optionalWidgetInstantiationCallback){
            var self = this, instantiatingWidgets = [], widgets = [], theFormLayout, tableAtts = layout.tableAtts;
            if (tableAtts && layout.widgets){
                this.addChild(theFormLayout = new FormLayout({columns: (tableAtts.showLabels && tableAtts.orientation!== 'vert') ? 'two' : 'single'}));
            	layout.widgets.forEach(lang.hitch(this, function(widgetName){
                    var widgetDescription = this.widgetsDescription[widgetName], widgetAtts = widgetDescription.atts, instantiatingWidget, widgetType, widgetLayout, widgetLabel, widgetFieldSet;
                	if (widgetDescription && (widgetType = (widgetDescription.atts.mobileWidgetType || mobileWidgetTypes[widgetDescription['type']] || widgetDescription['type']))){
    	                self.widgetsName.push(widgetName);
                    	widgetLayout = dct.create('div', null, theFormLayout.domNode);
                    	if (tableAtts.showLabels){
                    		widgetLabel = dct.create('label', {innerHTML: widgetDescription.atts.label}, widgetLayout);
                    	}
                		widgetFieldSet = dct.create('fieldset', null, widgetLayout);
                		dojo.when(instantiatingWidget = widgetsLoader.instantiate(widgetType, utils.mergeRecursive(widgetAtts, {id: this.id + widgetName, pane: this, form: this, widgetType: widgetType, widgetName: widgetName,
                											style: {backgroundColor: widgetAtts.disabled ? 'lightgrey': 'white', color: 'black'}}), optionalWidgetInstantiationCallback), function(theWidget){
                    		theWidget.layoutHandle = self;
                    		theWidget.layoutContainer = widgetLayout;
                    		if (theWidget.get('hidden')){
                    			dst.set(widgetLayout, 'display', 'none');
                    		}
                			widgetFieldSet.appendChild(theWidget.domNode);
                    		self.decorate(theWidget);
                    		//if (self._started){
                    			theWidget.startup();
                    		//}
                    	});
                        if (typeof instantiatingWidget.then === "function"){
                        	this.instantiatingWidgets[widgetName] = instantiatingWidget;
                        }
                	}
            	}));
            }
            for (var item in layout.contents){
                this.layout(layout.contents[item], optionalWidgetInstantiationCallback);
            }
        },
        layoutAction: function(layout, optionalWidgetInstantiationCallback){
            var self = this, instantiatingWidgets = [], widgets = [], tableAtts = layout.tableAtts, actionsHeading = this.viewPane.actionsHeading;
            if (tableAtts && layout.widgets){
            	layout.widgets.forEach(lang.hitch(this, function(widgetName){
                    var widgetDescription = this.widgetsDescription[widgetName], instantiatingWidget, widgetType;
                	if (widgetName !== 'feedback' && widgetDescription && (widgetType = (mobileWidgetTypes[widgetDescription['type']] || widgetDescription['type']))){
    	                self.widgetsName.push(widgetName);
                		dojo.when(instantiatingWidget = widgetsLoader.instantiate(widgetType, utils.mergeRecursive({id: this.id + widgetName, style: {backgroundColor: 'DarkGrey', paddingLeft: 0, paddingRight: 0, fontSize: '12px'}, pane: this,
                			form: this, widgetType: widgetType, widgetName: widgetName}, widgetDescription['atts']), optionalWidgetInstantiationCallback), function(theWidget){
                				actionsHeading.addChild(theWidget);
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
        onInstantiated: function(callback){
            if (!utils.empty(this.instantiatingWidgets)){
                return all(this.instantiatingWidgets).then(lang.hitch(this, function(results){
                    return callback();
                }));
            }else{
                return callback();
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
