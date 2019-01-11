define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/ready", "dijit/layout/ContentPane", "tukos/widgetUtils", "tukos/_TukosLayoutMixin", "tukos/_TukosPaneMixin",  "tukos/widgets/WidgetsHider"], 
    function(declare, lang, dct, ready, ContentPane, wutils, _TukosLayoutMixin, _TukosPaneMixin, WidgetsHider){
    return declare([ContentPane, _TukosLayoutMixin, _TukosPaneMixin], {
        postCreate: function(){
            this.inherited(arguments);
            this.widgetType = "TukosPane";
            this.widgetsName = [];
            this.changedWidgets = {};
            this.userChangedWidgets = {};
            if (this.layout){
            	this.loadPane();
            }

        },
        onShow: function(evt){
        	if (this.layout && !this.isLoaded){
        		this._started = false;
        		this.loadPane();
        		this.startup();
        		Pmg.setFeedback('JC: in TukosPane, onShow and was not loaded!');
            }
        },
        
        loadPane: function(){
        	var paneTable = this.tableLayout(this.layout, this, lang.hitch(wutils, wutils.setWatchers)); 
            this.onInstantiated(lang.hitch(this, function(){
                this.markIfChanged = true;
                this.watchOnChange = true;
                this.watchContext = 'user';
                if (this.widgetsHider){
                    var hiderArgs = this.widgetsHiderArgs || {}, spanOrDiv = hiderArgs.span || "div", place = hiderArgs.place, widgetsHider = new WidgetsHider(lang.mixin({form: this}, hiderArgs), dojo.doc.createElement(spanOrDiv));
                    if (place){
                        ready(function(){
                            dct.place(widgetsHider.domNode, place.refNode, place.where);                        	
                        });                        	
                    }else{
                    	this.addChild(widgetsHider);
                    }
                }
            }));
            this.isLoaded = true;     	
        }
    }); 
});


