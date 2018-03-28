define (["dojo/_base/declare", 	"dojo/_base/lang", "dojo/dom-attr", "dojo/on", "dojo/when", "dijit/form/FilteringSelect", "tukos/utils", "tukos/widgetUtils", "tukos/_WidgetsMixin", "tukos/PageManager", "dojo/json"], 
    function(declare, lang, domAttr, on, when, FilteringSelect, utils, wutils, _WidgetsMixin, Pmg, JSON){
    return declare([FilteringSelect, _WidgetsMixin], {
        constructor: function(args){

        	args.storeArgs = args.storeArgs || {};
            args.storeArgs.view = args.storeArgs.view || 'noview';
            args.storeArgs.action = args.storeArgs.action || 'objectselect';
            //if (args.storeArgs.object || args.object){
                args.storeArgs.object = args.storeArgs.object || args.object;
            //}
            //if (args.storeArgs.mode || args.mode){
                args.storeArgs.mode = args.storeArgs.mode || args.mode || 'tab';
            //}
            args.store = Pmg.store(args.storeArgs);

        	declare.safeMixin(this, args);
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

        toggleDropDown: function(){
            this.query = {};
            for (var i in this.dropdownFilters){
                var theCol = this.dropdownFilters[i];
                if (typeof(theCol) == "string" && wutils.specialCharacters.indexOf(theCol[0]) > -1){
                    this.query[i] = this.valueOf(theCol);
                }else{
                    this.query[i] = theCol;
                }
            }
            this.inherited(arguments);
        },

        onFocus: function(){
            for (var i in this.dropdownFilters){
                var theCol = this.dropdownFilters[i];
                if (typeof(theCol) == "string" && wutils.specialCharacters.indexOf(theCol[0]) > -1){
                    this.query[i] = this.valueOf(theCol);
                }else{
                    this.query[i] = theCol;
                }
            }
            this.inherited(arguments);
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
