define (["dojo/_base/declare", 	"dojo/_base/lang", "dojo/dom-attr", "dojo/on", "dojo/when", "dojo/Deferred", "dijit/form/FilteringSelect", "tukos/utils", "tukos/widgetUtils", "tukos/PageManager", "dojo/json"/*, "dojo/domReady!"*/], 
    function(declare, lang, domAttr, on, when, Deferred, FilteringSelect, utils, wutils, Pmg, JSON){
    return declare([FilteringSelect], {
        constructor: function(args){
            args.storeArgs = args.storeArgs || {};
            args.storeArgs.view = args.storeArgs.view || 'NoView';
            args.storeArgs.action = args.storeArgs.action || 'RestSelect';
            args.storeArgs.object = args.storeArgs.object || args.object || 'noObject';
            args.store = Pmg.store(args.storeArgs);
        },
	    postCreate: function(){
	        var self = this;
	        this.inherited(arguments);
	        this.watch("displayedValue", function(name, oldValue, newValue){
	            var matchArray = newValue.match(/(.*)\(([^)]*)\)$/);
	            if (matchArray){
	                var obj = {};
	                var id = matchArray[2];
	                obj[id] = {name: matchArray[1], object: self.object};
	                Pmg.addExtrasToCache(obj);
	            }
	        });
	    },
        _setValueAttr: function(/*String*/ value, /*Boolean?*/ priorityChange, /*String?*/ displayedValue, /*item?*/ item){
            if (item === undefined && displayedValue === undefined && (value === null || value === '')){
                this.inherited(arguments, [value, priorityChange, '', item]);
            }else{
                return this.inherited(arguments);
            }
        }
    }); 
});
