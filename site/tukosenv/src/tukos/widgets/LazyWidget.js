"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dojo/when", "dojo/dom-style", "dijit/_WidgetBase", "tukos/PageManager", "tukos/widgets/WidgetsLoader"], 
  function(declare, lang, ready, when, domStyle, _WidgetBase, Pmg, WidgetsLoader){
	return declare([_WidgetBase], {
		postCreate: function postCreate (){
			this.inherited(postCreate, arguments);
			if (!this.hidden){
				this.unhide();
			}
		},
		unhide: function(){
			if (!this.widget){
				const self = this, props = {}, params = this.params;
				this.destroyRecursive();
				when(WidgetsLoader.instantiate(params.unhideWidget, params), lang.hitch(this, function(widget){
					['disabled', 'value', 'form'].forEach(function(property){
						props[property] = self.get(property);
					});
					for (let property in props){
						widget.set(property, props[property]);
					}
					lang.mixin(this, widget);
					this.widget = this;
					this.hidden = false;
					//this.widget.form.resize();
				}));
			}
		},
		_setHiddenAttr: function(value){
			if (!value){
				this.unhide();
			}else{
				this.hidden = true;
			}
		}
	});
}); 
