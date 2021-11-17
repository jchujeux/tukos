define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dijit/layout/ContentPane", "tukos/widgetUtils", "tukos/_TukosLayoutMixin", "tukos/_TukosPaneMixin"], 
    function(declare, lang, dct, ContentPane, wutils, _TukosLayoutMixin, _TukosPaneMixin){
    return declare([ContentPane, _TukosLayoutMixin, _TukosPaneMixin], {
        postCreate: function(){
            this.inherited(arguments);
            this.widgetType = "TukosPane";
            this.widgetsName = [];
            this.customization = {};
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
        	var self = this;
			this.tableLayout(this.layout, this, lang.hitch(wutils, wutils.setWatchers)); 
            this.onInstantiated(lang.hitch(this, function(){
                this.markIfChanged = true;
                this.watchOnChange = true;
                this.watchContext = 'user';
                if (this.widgetsHider){
					require(["tukos/WidgetsHiderButton"], function(WidgetsHiderButton){
						self.widgetsHiderButton = new WidgetsHiderButton({form: self, 'class': 'ui-icon dgrid-hider-toggle'});
						self.widgetsHiderButton.set('iconClass', 'ui-icon dgrid-hider-toggle');
						dct.place(self.widgetsHiderButton.domNode, self.domNode);
					});
                }
            }));
            this.isLoaded = true;     	
        }
    }); 
});


