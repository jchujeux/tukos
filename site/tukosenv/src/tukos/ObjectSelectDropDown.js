/*
    Provides a popup menu to allow object selection from a 'sub' widget attached to the widget dropdown
    Currently supported dropdown widgets:
    - StoreTree
*/
define (["dojo/_base/declare", "dojo/on", "dijit/form/MappedTextBox", "dijit/_HasDropDown", "tukos/PageManager", "tukos/StoreTree",
         "dojo/text!dijit/form/templates/DropDownBox.html"], 
    function(declare, on, MappedTextBox, _HasDropDown, Pmg, StoreTree, template){
    return declare([MappedTextBox, _HasDropDown], {
        templateString: template,
        baseClass: "dijitTextBox dijitComboBox",
        cssStateNodes: {
                "_buttonNode": "dijitDownArrowButton"
        },
        validate: function(){
                // to fix error in dijit/form/ValidationTextBox.validate when called as an editor in dgrid
                if (this.textbox != undefined){
                    return this.inherited(arguments);
                }else{
                    return true;
                }
        },
        
        openDropDown: function(){
            if (!this.dropDown){
                var dropDown = this.dropDownWidget, type = dropDown['type'], atts = dropDown['atts'], self = this;
                if (type === 'StoreTree'){
                	var theDropDown = this.dropDown = new StoreTree(atts);
                    theDropDown.onClick = function(evt){
                        var obj = {};
                        obj[this.selectedItem.id] = {name: this.selectedItem.name, object: self.object};
                        Pmg.addExtendedIdsToCache(obj);
                        self.set('value', this.selectedItem.id);
                        self.closeDropDown();
                    }
                }else{
                	console.log('ObjectSelectDropDown: only StoreTree dropDown is currently supported');
                }
            }
            this.inherited(arguments);
        },

        format: function(value){
            return (value == 0 || value == '') ? '' : Pmg.namedId(value);
        },
        parse: function(displayedValue){
            var matchArray = displayedValue.match(/(.*)\((\d*)\)$/);
            return (matchArray ? matchArray[2] : '');
        }
    }); 
});
