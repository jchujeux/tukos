define (["dojo/_base/declare", "dojo/_base/lang", "dojo/Deferred", "dojo/promise/all",  "dojoFixes/dojox/layout/TableContainer", "tukos/PageManager", "tukos/utils", "tukos/widgetUtils", "tukos/widgets/WidgetsLoader"], 
    function(declare, lang, deferred, all, TableContainer, Pmg, utils, wutils, widgetsLoader){
    return declare(null, {

        constructor: function(){
            this.instantiatingWidgets = {};
        },

           
        tableLayout:    function(layout, fromParent, optionalWidgetInstantiationCallback){
            var instantiatingWidgets = [],
                  widgets = [];
            if (layout.tableAtts){
                var parent  = new TableContainer(layout.tableAtts, dojo.doc.createElement('div'));
                fromParent.addChild(parent);
            }else{
                var parent = fromParent;
            }
            for (var item in layout.contents){
                parent.addChild(this.tableLayout(layout.contents[item], parent, optionalWidgetInstantiationCallback));
            }
            for (var i in layout.widgets){
                var widgetName = layout.widgets[i], widgetDescription = this.widgetsDescription[widgetName];
                if (widgetDescription){
	                var theDijitType = this.widgetsDescription[widgetName]['type'];
	                var theDijitAtts  = this.widgetsDescription[widgetName]['atts'];
	                this.widgetsName.push(widgetName);
	                theDijitAtts.id  = this.id + widgetName;
	                theDijitAtts.pane  = theDijitAtts.form = this;
	                theDijitAtts.widgetType = theDijitType;
	                theDijitAtts.widgetName = widgetName;
	                var instantiatingWidget = widgetsLoader.instantiate(theDijitType, theDijitAtts, optionalWidgetInstantiationCallback);
	                //console.log('widget: ' + widgetName);
	                if (typeof instantiatingWidget.then === "function"){
	                    this.instantiatingWidgets[widgetName] = instantiatingWidgets[i] = widgets[i] = instantiatingWidget;
	                }else{
	                    widgets[i] = instantiatingWidget;
	                }
	                dojo.when(instantiatingWidget, lang.hitch(this, function(widget){
	                    widget.layoutHandle = this;
	                    this.decorate(widget);
	                 }));
                }else{
                	Pmg.addFeedback('no widgetDescription for widget: ' + widgetName);
                }
            }
            
            if (! utils.empty(instantiatingWidgets)){
                all(instantiatingWidgets).then(lang.hitch(this, 
                    function(parent, instantiatedWidgets){
                        for (var i in widgets){
                            parent.addChild(instantiatedWidgets[i] || widgets[i]);
                        }
                    },
                    parent
                ));
            }else{
                for (var i in widgets){
                    parent.addChild(widgets[i]);
                }
            }
            return parent;
        },
        
        onInstantiated: function(callback){
            if (!utils.empty(this.instantiatingWidgets)){
                return all(this.instantiatingWidgets).then(lang.hitch(this, function(results){
                    return callback();
                    //this.deferredInstantiatingWidgets = {};
                }));
            }else{
                return callback();
            }
        }
    });
});

