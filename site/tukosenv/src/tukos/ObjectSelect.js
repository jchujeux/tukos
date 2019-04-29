define (["dojo/_base/declare", 	"dojo/_base/lang", "dojo/dom-attr", "dojo/on", "dojo/when", "dijit/form/FilteringSelect", "tukos/utils", "tukos/widgetUtils", "tukos/PageManager", "dojo/json"], 
    function(declare, lang, domAttr, on, when, FilteringSelect, utils, wutils, Pmg, JSON){
    return declare([FilteringSelect], {
        postCreate: function(){
            var self = this;
            this.store = Pmg.store(lang.mixin({object: this.object, view: 'NoView', mode: this.mode || 'Tab', action: 'ObjectSelect'}, this.storeArgs));
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
