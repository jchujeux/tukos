define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dijit/layout/ContentPane", "tukos/widgetUtils", "tukos/_TukosLayoutMixin", "tukos/_TukosPaneMixin", "tukos/PageManager"], 
    function(declare, lang, dct, ContentPane, wutils, _TukosLayoutMixin, _TukosPaneMixin, Pmg){
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
            this.watchOnChange = true;
			this.tableLayout(this.layout, this, lang.hitch(wutils, wutils.setWatchers), this.commonWidgetsAtts); 
            this.onInstantiated(lang.hitch(this, function(){
               /* setTimeout(lang.hitch(this, function(){// needed due to a setTimeout in _WidgetBase.defer causing problem of markIfChanged being true in the onCHange event of SliderSelect (at least)
                		this.markIfChanged = true;
						this.watchOnChange = true;
                        this.watchContext = 'user';
                        //this.setUserContextPaths(); 
                	}), 0);*/
                this.markIfChanged = true;
                this.watchContext = 'user';
                if (this.widgetsHider && !Pmg.isRestrictedUser()){
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


