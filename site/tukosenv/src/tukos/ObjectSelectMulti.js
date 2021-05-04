/*
    Provides a popup menu to allow parent selection in case of multi-table parent  
    - usage: 
*/
define (["dojo/_base/declare", "dojo/dom-attr", "dijit/popup", "dijit/PopupMenuItem", "dijit/MenuItem", "dijit/DropDownMenu", "dijit/form/MappedTextBox",
         "dijit/_HasDropDown", "tukos/PageManager", "dijit/Tooltip",  "tukos/widgets/WidgetsLoader",
         "dojo/text!dijit/form/templates/DropDownBox.html"], 
    function(declare, domAttr, popup, PopupMenuItem, MenuItem, DropDownMenu, MappedTextBox, _HasDropDown, Pmg, Tooltip, widgetsLoader, template){
    return declare([MappedTextBox, _HasDropDown], {
        templateString: template,
		baseClass: "dijitTextBox dijitComboBox",
		cssStateNodes: {
			"_buttonNode": "dijitDownArrowButton"
		},
        constructor: function(args){
            if (!args.allowManualInput){
            	args.onInput = function(event){
                    Tooltip.show(Pmg.message('Usedropdown'), this.domNode, this.tooltipPosition,!this.isLeftToRight()); 
                    return false;
                }           	
            }
        },
        postCreate: function(){
        	this.inherited(arguments);
        	this.object = this.defaultObject;
        },
		_onFocus: function(){
			if (Pmg.isMobile()){
				domAttr.set(this.textbox, 'readonly', 'readonly');
			}
		},
        openDropDown: function(){
            var self = this;
            var onChangeCallBack = function(newValue){
                self.set('value', newValue);
                self.object = newValue ? this.object : self.defaultObject;
				this.set('value', '', false);
                self.closeDropDown();
            }
            if (!this.dropDown){
                this.dropDown = new DropDownMenu({onItemClick: function(item, evt){
                    if (item.popup === self.emptyDropDown){
                        dojo.when(widgetsLoader.instantiate('ObjectSelect', item.objectSelectAtts), function(theObjectSelect){
                        	item.set('popup', theObjectSelect);
							popup.open({parent: item, popup: item.popup, around: item.domNode, orient: Pmg.isMobile() ? undefined : ['after-centered'], onExecute: function(){popup.close(item.popup);}, onCancel: function(){popup.close(item.popup);}, onClose: function(){}});
                        });
                    }else if (item.popup){
						popup.open({parent: item, popup: item.popup, around: item.domNode, orient: Pmg.isMobile() ? undefined: ['after-centered'], onExecute: function(){popup.close(item.popup);}, onCancel: function(){popup.close(item.popup);}, onClose: function(){}});
					}else{
						self.set('value', '');
					}
				}});
                this.emptyDropDown = new DropDownMenu({});
				this.dropDown.addChild(new MenuItem());
                if (self.items){
                    for (var i in self.items){
                        self.items[i].onChange = onChangeCallBack;
                        self.items[i].form = this.form || this.getParent().form;
                        this.dropDown.addChild(new PopupMenuItem({label: self.items[i].label, popup: this.emptyDropDown, objectSelectAtts: self.items[i], onMouseOver: function(evt){evt.preventDefault();evt.stopPropagation();}/*, onMouseUp: mouseOutCallback*/}));
                    }
                    this.inherited(arguments);
                }else{
	                var _arguments = arguments;
                	Pmg.serverDialog({action: 'Get', view: 'NoView', mode: this.form.paneMode, object: this.form.object, 
                					  query: {params: {actionModel: 'GetObjectModules'}, actioncontextpathid: this.form.tabContextId(), contextid: self.form.valueOf('id')}}, [], [], Pmg.message('actionDone')).then(
	                	function(response){
	                        var items = response.modules;
	                		for (var i in items){
	                            items[i].onChange = onChangeCallBack;
	                            items[i].form = self.form || self.getParent().form;
	                            self.dropDown.addChild(new PopupMenuItem({label: items[i].label, popup: self.emptyDropDown, objectSelectAtts: items[i], onMouseOver: function(evt){evt.preventDefault();evt.stopPropagation();}}));
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
        },
        _setValueAttr: function(value){
        	this.inherited(arguments);
        	this.object = value ? Pmg.objectName(value) : this.defaultObject;
        }

    });
});
