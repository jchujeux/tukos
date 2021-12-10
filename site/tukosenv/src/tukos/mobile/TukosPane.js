define(["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "dojox/mobile/Container", "tukos/_TukosPaneMixin", "tukos/_TukosLayoutMixin", "tukos/widgetUtils", "tukos/PageManager"], 
    function(declare, lang, when, Container, _TukosPaneMixin, _TukosLayoutMixin, wutils, Pmg){
	return declare([Container, _TukosPaneMixin, _TukosLayoutMixin], {
        postCreate: function(){
        	this.inherited(arguments);
        	this.Pmg = Pmg;
            this.widgetType = "MobileTukosPane";
            this.widgetsName = [];
            this.customization = {};
            this.changedWidgets = {};
            this.userChangedWidgets = {};
			if (this.layout){
				this.loadPane();
			}
        },
        loadPane: function(){
			this.tableLayout(this.layout, this, lang.hitch(wutils, wutils.setWatchers), this.commonWidgetsAtts);
            this.watchOnChange = true;
            this.watchContext = 'server';
            this.onInstantiated(lang.hitch(this, function(){
                dojo.ready(lang.hitch(this, function(){
					this.resize();//resize needed here due to specific behavior of SpinWheelSlots
					when (this.setWidgets(this.data), lang.hitch(this, function(){
	                    if (this.onOpenAction){
	                        this.openAction(this.onOpenAction);
	                    }
	                    setTimeout(lang.hitch(this, function(){// needed due to a setTimeout in _WidgetBase.defer causing problem of markIfChanged being true in the onCHange event of SliderSelect (at least)
	                    		this.markIfChanged = true;
								this.watchOnChange = true;
	                            this.watchContext = 'user';
	                            //this.setUserContextPaths(); 
	                    	}), 0);
	                }));
				}));
            }));
        }
    });
});
