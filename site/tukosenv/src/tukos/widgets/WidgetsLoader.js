define (["dojo/_base/lang", "dojo/_base/Deferred", "dojo/when", "tukos/PageManager"],
		function(lang, Deferred, when, Pmg){
	var widgetsBeingInstantiated = 0;
	return {
            widgetsPath: {
                TextBox: "dijit/form/", NumberTextBox: "dijit/form/", CurrencyTextBox: "dijit/form/", CheckBox: "tukos/widgets/", TimeTextBox: "tukos/widgets/", Textarea: "dijit/form/", Select: "dijit/form/", Button: "dijit/form/",
                DropDownButton: "dijit/form/", RadioButton: "dijit/form/", Menu: "dijit/", MenuItem: "dijit/", MenuBarItem: "dijit/", PopupMenuItem: "dijit/", PopupMenuBarItem: "dijit/", DropDownMenu: "dijit/", ContentPane: "dijit/layout/",
                TukosDateBox: "tukos/", Editor: "tukos/widgets/", LazyEditor: "tukos/widgets/",
                FormattedTextBox: "tukos/widgets/", SearchTextBox: "tukos/widgets/", MultiSelect: "tukos/", StoreSelect: "tukos/", ObjectSelect: "tukos/", ObjectSelectMulti: "tukos/", ObjectSelectDropDown: "tukos/",
                TukosNumberBox: "tukos/", TukosCurrencyBox: "tukos/", NumberUnitBox: "tukos/", DateTimeBox: "tukos/", TukosButton: "tukos/widgets/", TukosRadioButton: "tukos/widgets/", DropDownTextBox: "tukos/widgets/", 
                ColorPickerTextBox: "tukos/widgets/", RestSelect: "tukos/", ObjectExport: "tukos/", ObjectSave: "tukos/", ObjectReset: "tukos/", ObjectProcess: "tukos/", ObjectNew: "tukos/", ObjectEdit: "tukos/", ObjectDelete: "tukos/", 
                ObjectDuplicate: "tukos/", ObjectCalendar: "tukos/", ObjectFieldClear: "tukos/", OverviewAction: "tukos/", OverviewEdit: "tukos/", OnDemandGrid: "tukos/", SimpleDgrid: "tukos/",  StoreDgrid: "tukos/",  StoreDgridNoDnd: "tukos/", SimpleDgridNoDnd: "tukos/",
                OverviewDgrid: "tukos/", BasicGrid: "tukos/", StoreTree: "tukos/", ContextTree: "tukos/", NavigationTree: "tukos/", PieChart: "tukos/", ColumnsChart: "tukos/", Chart: "tukos/",
                SimpleUploader: "tukos/widgets/", Uploader: "tukos/widgets/", Downloader: "tukos/widgets/", StoreCalendar: "tukos/widgets/calendar/", StoreSimpleCalendar: "tukos/widgets/calendar/", 
                widgetsHider: "tukos/widgets/", HorizontalLinearGauge: "tukos/widgets/dgauges/",
                ObjectEditor: "tukos/widgets/", HtmlContent: "tukos/widgets/", StoreComboBox: "tukos/", TukosTextarea: "tukos/widgets/", ColorButton: "tukos/widgets/", ComboBox: "dijit/form/", TukosTooltipDialog: "tukos/",
                MobileTextBox: "tukos/mobile/TukosTextarea*", MobileButton: "dojox/mobile/Button*",
                MobileFormattedTextBox: "tukos/mobile/FormattedTextBox*", MobileEditor: "tukos/mobile/Editor*", MobileLazyEditor: "tukos/mobile/LazyEditor*", MobileObjectReset: "tukos/mobile/ObjectAction*",
                MobileObjectAction: "tukos/mobile/ObjectAction*", MobileOverviewGrid: "tukos/mobile/OverviewGrid*", MobileOverviewAction: "tukos/mobile/OverviewAction*", MobileStoreSelect: "tukos/mobile/StoreSelect*",
                MobileSliderSelect: "tukos/mobile/SliderSelect*", MobileTimePicker: "tukos/mobile/TimePicker*", MobileNumberBox: "tukos/mobile/DecimalNumberPicker*", MobileStoreCalendar: "tukos/mobile/StoreCalendar,*"/*, TukosCheckBox: "tukos/Widgets/Checkbox*"*/,
				MobileTukosPane: "tukos/mobile/TukosPane*", MobileAccordionGrid: "tukos/mobile/AccordionGrid*", DecimalNumberPicker: "tukos/mobile/DecimalNumberPicker*"
            },
			mobileWidgetTypes: {TextBox: 'MobileTextBox', FormattedTextBox: 'MobileFormattedTextBox'/*, LazyEditor: 'LazyEditor', ObjectReset: 'MobileObjectReset', ObjectSave: 'MobileObjectAction', ObjectNew: 'MobileObjectAction', StoreSimpleCalendar: "MobileStoreCalendar",
				StoreCalendar: "MobileStoreCalendar"*/, OverviewDgrid: 'MobileOverviewGrid'/*, OverviewAction: 'MobileOverviewAction'*/, Textarea: 'MobileTextBox'/*, StoreSelect: "MobileStoreSelect"*/, TimeTextBox: "MobileTimePicker", TukosNumberBox: "MobileNumberBox", StoreDgrid: "StoreDgridNoDnd",
				SimpleDgrid: "SimpleDgridNoDnd"},
            loadedWidgets: {}, loadingWidgets: {},
			instantiationCompleted: function(){
				if (widgetsBeingInstantiated){
					var instantiationDfd = new Deferred(true), watcher;
					watcher = setInterval(function(){
							if (!widgetsBeingInstantiated){
								instantiationDfd.resolve();
								clearInterval(watcher);
							}
						}, 100);
					return instantiationDfd;
				}else{
					return true;
				}
			},            
			instantiate: function(requiredWidgetType, atts, optionalWidgetInstantiationCallback){
				var forcedMobileWidget = Pmg.isMobile() && atts.mobileWidgetType, widgetType = forcedMobileWidget ? atts.mobileWidgetType : requiredWidgetType;
				widgetsBeingInstantiated += 1;
				return when(this.loadWidget(widgetType, forcedMobileWidget), lang.hitch(this, function(Widget){
                    return when (this.loadDependingWidgets(Widget, widgetType, atts), lang.hitch(this, function(Widget){
                        return this._instantiate(Widget, atts, optionalWidgetInstantiationCallback);
                    }));
                }));
            },
            _instantiate: function(Widget, atts, optionalWidgetInstantiationCallback){// requires Widgets and its dependingWidgets to be loaded
                var widget = new Widget(atts);
                if (optionalWidgetInstantiationCallback){
                    optionalWidgetInstantiationCallback(widget);
                }
				widgetsBeingInstantiated += -1;                
				return widget;
            },
            loadWidget: function(requiredWidgetType, forcedMobileWidget){
			var widgetType = forcedMobileWidget || !Pmg.isMobile() ? requiredWidgetType : this.mobileWidgetTypes[requiredWidgetType] || requiredWidgetType;
			if (this.loadedWidgets[widgetType]){
                    return this.loadedWidgets[widgetType];
                }else if(this.loadingWidgets[widgetType]){
                	return this.loadingWidgets[widgetType];
                }else{
                    var location = this.widgetLocation(widgetType) || null;
                    if (location){
                        this.loadingWidgets[widgetType] =  new Deferred();
                    	require([location], lang.hitch(this, function(Widget){
                            this.loadedWidgets[widgetType] = Widget;
                            this.loadingWidgets[widgetType].resolve(Widget);
                        }));
                        return this.loadingWidgets[widgetType];
                    }else{
                    	console.log('programmer error - Loading widget - unknown widgetType: ' + widgetType);
                    	return null;
                    }
                }
            }, 
            loadDependingWidgets: function(Widget, widgetType, atts){
                if (Widget.loadDependingWidgets){
                	return Widget.loadDependingWidgets(Widget, atts);
                }
                return Widget;
            },
            widgetLocation: function(widgetType){
                var path = this.widgetsPath[widgetType], flag = path.slice(-1);
                if (flag === '*'){// introduced for mobile widgets that may have same name as dijit widgets
                	return path.slice(0, -1);
                }else{
                	return this.widgetsPath[widgetType] + widgetType;
            	}
            }
        };
    }
);
