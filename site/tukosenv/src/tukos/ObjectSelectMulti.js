/*
    Provides a popup menu to allow parent selection in case of multi-table parent  
    - usage: 
*/
define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dijit/PopupMenuItem", "dijit/DropDownMenu", "dijit/form/MappedTextBox",
         "dijit/_HasDropDown", "tukos/utils", "tukos/PageManager", "dijit/Tooltip", "dijit/form/TextBox",  "tukos/widgets/WidgetsLoader",
         "dojo/text!dijit/form/templates/DropDownBox.html", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, lang, on, PopupMenuItem, DropDownMenu, MappedTextBox, _HasDropDown, utils, Pmg, Tooltip, TextBox, widgetsLoader, template, messages){
    return declare([MappedTextBox, _HasDropDown], {
        templateString: template,
		baseClass: "dijitTextBox dijitComboBox",
		cssStateNodes: {
			"_buttonNode": "dijitDownArrowButton"
		},
        constructor: function(args){
            args.onInput = function(event){
                Tooltip.show("manual entry not allowed. Use dropdown", this.domNode, this.tooltipPosition,!this.isLeftToRight()); 
                return false;
            }
        },
        openDropDown: function(){
            var self = this;
            var onChangeCallBack = function(newValue){
                self.set('value', newValue);
                self.closeDropDown();
            }
            var mouseOverCallBack = function(evt){
                var self1 = this;
                this.timeout = setTimeout(
                    function(){
                        if (self1.popup === self.emptyDropDown){
                            dojo.when(widgetsLoader.instantiate('ObjectSelect', self1.objectSelectAtts), function(theObjectSelect){
                            	self1.set('popup', theObjectSelect);                            	
                            });
                        	//self1.set('popup', new ObjectSelect(self1.objectSelectAtts));
                        }
                    },
                    200
                );
            }
            var mouseOutCallback = function(evt){
                clearTimeout(this.timeout);
            }
            if (!this.dropDown){
                this.dropDown = new DropDownMenu({});
                this.emptyDropDown = new DropDownMenu({});
                if (self.items){
                    for (var i in self.items){
                        self.items[i].onChange = onChangeCallBack;
                        self.items[i].form = this.form || this.getParent().form;
                        this.dropDown.addChild(new PopupMenuItem({label: self.items[i].label, popup: this.emptyDropDown, objectSelectAtts: self.items[i], onMouseOver: mouseOverCallBack, onMouseOut: mouseOutCallback}));
                    }
                    this.inherited(arguments);
                }else{
	                var _arguments = arguments;
                	Pmg.serverDialog({action: 'Get', view: 'NoView', mode: this.form.paneMode, object: this.form.object, 
                					  query: {params: {actionModel: 'getObjectModules'}, actioncontextpathid: this.form.tabContextId(), contextid: self.form.valueOf('id')}}, [], [], messages.actionDone).then(
	                	function(response){
	                        var items = response.modules;
	                		for (var i in items){
	                            items[i].onChange = onChangeCallBack;
	                            items[i].form = self.form || self.getParent().form;
	                            self.dropDown.addChild(new PopupMenuItem({label: items[i].label, popup: self.emptyDropDown, objectSelectAtts: items[i], onMouseOver: mouseOverCallBack, onMouseOut: mouseOutCallback}));
	                        }
	                        self.inherited(_arguments);	
	                }); 
                }
            }else{
                this.inherited(arguments);
            }
         },

        format: function(value){
            if (value == 0 || value == ''){
                return "";
            }else{
                return Pmg.namedId(value);
            }
        },
        parse: function(displayedValue){
            var matchArray = displayedValue.match(/(.*)\((\d*)\)$/);
            return (matchArray ? matchArray[2] : '');
        }

    });
});
