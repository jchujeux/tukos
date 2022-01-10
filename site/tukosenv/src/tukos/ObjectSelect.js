define (["dojo/_base/declare", 	"dojo/_base/lang", "dojo/dom", "dojo/dom-attr", "dojo/aspect", "dojo/ready", "dijit/form/FilteringSelect", "dijit/focus", "tukos/PageManager"], 
    function(declare, lang, dom, domAttr, aspect, ready, FilteringSelect, focusUtil, Pmg){
	var clickedNode = null;
	aspect.before(focusUtil, '_onTouchNode', function(node, by) {
	    clickedNode = node;
	    return [node, by];
	});
	/*focusUtil.on("widget-focus", function(widget){
		console.log("focused widget: ", widget);
	});*/
    return declare([FilteringSelect], {
		_firstClick: true,
		constructor: function(args){
        	args.store = Pmg.store(lang.mixin({object: args.object, view: 'NoView', mode: args.mode || 'Tab', action: 'ObjectSelect', widget: this}, args.storeArgs));
        },
    	postCreate: function(){
            var self = this;
            this.inherited(arguments);
            this.watch("displayedValue", function(name, oldValue, newValue){
                var matchArray = newValue.match(/(.*)\((\d*)\)$/);
                if (matchArray){
                    var obj = {};
                    var id = matchArray[2];
                    obj[id] = {name: matchArray[1], object: self.object};
                    Pmg.addExtendedIdsToCache(obj);
                }
            });
        },
		onFocus: function(){
			if (Pmg.isMobile()){
            	var isButtonClicked = dom.isDescendant(clickedNode, this._buttonNode), textbox = this.textbox;
	            if (!this.textbox.disabled && (this._firstClick || isButtonClicked)) {
	                // disable focusing the input box on touch devices
	                // in order to avoid the keyboard from showing
	                //textbox.disabled = true;
					domAttr.set(textbox, 'readonly', 'readonly');
	                //if (this._firstClick && !isButtonClicked) {
	                    //this.toggleDropDown();
	                //}
	                this._firstClick = false;
	                setTimeout(function() {
	                    //textbox.disabled = false;
						domAttr.remove(textbox, 'readonly');
	                    clickedNode = null;
	                }, 1000);
	            }
			}
			this.inherited(arguments);
		},
		onBlur: function(){
			if (Pmg.isMobile()){
				this._firstClick = true;
				this.closeDropDown();
			}
			this.inherited(arguments);
		},
		_setValueAttr: function(/*String*/ value, /*Boolean?*/ priorityChange, /*String?*/ displayedValue, /*item?*/ item){
            if (item === undefined && displayedValue === undefined && (value === null || value === '')){
                this.inherited(arguments, [value, priorityChange, '', item]);
            }else{
                return this.inherited(arguments);
            }
        },
        getItem: function(){
        	return this.item;
        },
        getItemProperty: function(property){
        	return this.item[property];
        }
    }); 
});
