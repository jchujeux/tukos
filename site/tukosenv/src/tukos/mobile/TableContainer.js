define(["dojo/_base/declare", "dojo/dom-construct", "dojo/dom-style", "dojox/mobile/FormLayout", "dijit/_WidgetBase"], 
  function(declare, dct, dst, FormLayout, _WidgetBase){
    return declare([_WidgetBase, FormLayout], {
		constructor: function(args){
			this.columns = (args.showLabels && args.orientation !== 'vert') ? 'two' : 'single';
		},
		postCreate: function(){
			this.isLaidOut = false;
			this.isTableContainer = true;
			this.inherited(arguments);
        },
		startup: function() {
			if(this._started) {
				return;
			}
			this.inherited(arguments);
			if(this._initialized) {
				return;
			}
			var children = this.getChildren();
			if(children.length < 1) {
				return;
			}
			this._initialized = true;
	
			// Call startup on all child widgets
			children.forEach(function(child){
				if(!child.started && !child._started) {
					child.startup();
				}
			});
			this.layout();
	        //this.resize();
		},
		layout: function(){
			if (!this._initialized){
				return;
			}
			var self = this;
			this.getChildren().forEach(function(child){
				if (child.isTableContainer){
					child.layout();
				}else{
					if (!self.isLaidOut){
						var widgetLayout, widgetFieldSet;
						widgetLayout = dct.create('div', null, self.domNode);
						if (self.showLabels){
							dct.create('label', {innerHTML: child.label}, widgetLayout);
						}
						widgetFieldSet = dct.create('fieldset', null, widgetLayout);
						widgetFieldSet.appendChild(child.domNode);
					}
					dst.set(child.domNode.parentNode.parentNode, 'display', child.get('hidden') ? 'none' : '');
				}
			});
			this.isLaidOut = true;
		},
		resize: function(){
			this.layout();
			this.inherited(arguments);
		}
    });
});
