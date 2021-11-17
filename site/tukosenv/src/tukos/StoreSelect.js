define (["dojo/_base/declare", "dojo/_base/array", "dojo/_base/lang", "dojo/dom-attr", "dijit/form/FilteringSelect", "dojo/store/Memory", "tukos/widgetUtils", "tukos/PageManager"], 
    function(declare, arrayUtil, lang, domAttr, FilteringSelect, Memory, widgetUtils, Pmg){
    return declare([FilteringSelect], {
        constructor: function(args){
            if (args.dropdownFilters){
                args.storeData = args.storeArgs.data;
            }
            args.store= new Memory(args.storeArgs);
        },
		postCreate: function(){
			this.inherited(arguments);
		},
		_onFocus: function(){
			if (Pmg.isMobile()){
				domAttr.set(this.textbox, 'readonly', 'readonly');
			}
		},
        storeFilter: function(object){
            var match = previousMatch = newMatch = true;
            for (var i in this.dropdownFilters){
                var theCol = this.dropdownFilters[i];
                if (typeof (theCol) == "string"){
                	var testValue = widgetUtils.specialCharacters.indexOf(theCol[0]) > -1 ? this.valueOf(theCol) : theCol, okValues = object[i];
                    if (okValues === undefined){
                    	match = true;
                    }else if (okValues.constructor === Array){
                    	match = newMatch = previousMatch = okValues.indexOf(testValue) !== -1;
                    }else{
                        match = newMatch = previousMatch = (okValues == testValue) && previousMatch;                    	
                    }
                }else{
                    var opr = theCol['opr'];
                    var index = dojo.indexOf(theCol['values'], object[theCol['col']]);
                    newMatch = (opr == 'IN' && index >= 0) || (opr == 'NOT IN' && index == -1);
                    if (theCol['or'] != undefined && theCol['or']){
                        if (newMatch){
                            match = true; // It is a OR: if it is a match, we have a global match, else ignore & and continue the loop
                            break;
                        }else{
                            match = previousMatch = previousMatch && newMatch;
                        }
                    }else{
                        match = previousMatch = previousMatch && newMatch;//it is a AND: continue the loop and combine previousMatche(s) with current one
                    }
                }
            }
            return match;
        },
        toggleDropDown: function(){
		if (!this.isDisabled){
			if (this.dropdownFilters){
	                this.store.setData(arrayUtil.filter(this.storeData, lang.hitch(this, "storeFilter")));
	            }
	            this.inherited(arguments);
			}            
        },
		_getDisplayedValueAttr: function(){
			return this.inherited(arguments);
		},
		_setDisabledAttr: function(value){
			if (this.backgroundColors){
				this.isDisabled = value;
				this.set('readonly', value);
			}else{
				this.inherited(arguments);
			}
		},
		_setValueAttr: function(value){
			this.inherited(arguments);
			if (this.backgroundColors){
				this.set('style', {backgroundColor: this.backgroundColors[value]});
			}
		}
    }); 
});
