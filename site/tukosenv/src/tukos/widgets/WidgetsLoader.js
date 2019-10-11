define (["dojo/_base/declare", "dojo/_base/lang", "dojo/_base/Deferred", "dojo/promise/all", "dojo/when", "tukos/utils"],
		function(declare, lang, Deferred, all, when, utils){
	return {
            widgetsPath: {
                TextBox: "dijit/form/", NumberTextBox: "dijit/form/", CurrencyTextBox: "dijit/form/", CheckBox: "dijit/form/", TimeTextBox: "dijit/form/", Textarea: "dijit/form/", Select: "dijit/form/", Button: "dijit/form/",
                DropDownButton: "dijit/form/", RadioButton: "dijit/form/", Menu: "dijit/", MenuItem: "dijit/", PopupMenuItem: "dijit/", PopupMenuBarItem: "dijit/", DropDownMenu: "dijit/",
                TukosDateBox: "tukos/", Editor: "tukos/widgets/", LazyEditor: "tukos/widgets/",
                FormattedTextBox: "tukos/widgets/", MultiSelect: "tukos/", StoreSelect: "tukos/", ObjectSelect: "tukos/", ObjectSelectMulti: "tukos/", ObjectSelectDropDown: "tukos/",
                TukosNumberBox: "tukos/", TukosCurrencyBox: "tukos/", NumberUnitBox: "tukos/", DateTimeBox: "tukos/", TukosButton: "tukos/widgets/", DropDownTextBox: "tukos/widgets/", ColorPickerTextBox: "tukos/widgets/",
                RestSelect: "tukos/", ObjectExport: "tukos/", ObjectSave: "tukos/", ObjectReset: "tukos/", ObjectProcess: "tukos/", ObjectNew: "tukos/", ObjectEdit: "tukos/", ObjectDelete: "tukos/", ObjectDuplicate: "tukos/",
                ObjectCalendar: "tukos/", ObjectFieldClear: "tukos/", OverviewAction: "tukos/", OverviewEdit: "tukos/",  TukosDgrid: "tukos/", SimpleDgrid: "tukos/",  StoreDgrid: "tukos/", OverviewDgrid: "tukos/", ReadonlyGrid: "tukos/",
                BasicGrid: "tukos/", ContextTree: "tukos/", NavigationTree: "tukos/", PieChart: "tukos/", ColumnsChart: "tukos/", Chart: "tukos/", 
                SimpleUploader: "tukos/widgets/", Uploader: "tukos/widgets/", Downloader: "tukos/widgets/", StoreCalendar: "tukos/widgets/calendar/", StoreSimpleCalendar: "tukos/widgets/calendar/", 
                widgetsHider: "tukos/widgets/", HorizontalLinearGauge: "tukos/widgets/dgauges/",
                ObjectEditor: "tukos/widgets/", HtmlContent: "tukos/widgets/", StoreComboBox: "tukos/", TukosTextarea: "tukos/widgets/", ColorButton: "tukos/widgets/", ComboBox: "dijit/form/",
                MobileTextBox: "tukos/mobile/TukosTextarea*", MobileButton: "dojox/mobile/Button*",
                MobileFormattedTextBox: "tukos/mobile/FormattedTextBox*", MobileEditor: "tukos/mobile/Editor*", MobileLazyEditor: "tukos/mobile/LazyEditor*", MobileObjectReset: "tukos/mobile/ObjectAction*",
                MobileObjectAction: "tukos/mobile/ObjectAction*", MobileOverviewGrid: "tukos/mobile/OverviewGrid*", MobileOverviewAction: "tukos/mobile/OverviewAction*", MobileStoreSelect: "tukos/mobile/StoreSelect*",
                MobileTimePicker: "tukos/mobile/TimePicker*", MobileNumberBox: "tukos/mobile/NumberPicker*"
            },
            loadedWidgets: {}, loadingWidgets: {},

            instantiate: function(widgetType, atts, optionalWidgetInstantiationCallback){
                return when(this.loadWidget(widgetType), lang.hitch(this, function(Widget){
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
                return widget;
            },
            loadWidget: function(widgetType){
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
