define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-style", "dijit/_WidgetBase", "dijit/_FocusMixin", "dijit/form/TextBox", "tukos/StoreSelect", "tukos/widgetUtils", "tukos/utils", "dojo/json"], 
function(declare, lang, dst, Widget, _FocusMixin, TextBox, StoreSelect, wutils, utils, JSON){
    return declare([Widget, _FocusMixin], {
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            this.numberField = new TextBox(this.number, dojo.doc.createElement('span'));
            this.numberField.watch('value', lang.hitch(wutils, wutils.subWidgetWatchCallback, this, this.numberField));
            dst.set(this.numberField.domNode, 'display', 'none');
            this.domNode.appendChild(this.numberField.domNode); 
            this.unitField = new StoreSelect(this.unit, dojo.doc.createElement('span')); 
            this.unitField.watch('value', lang.hitch(wutils, wutils.subWidgetWatchCallback, this, this.unitField));
            this.unitField.on('change', function(newValue){
            	if (self.onChange){
            		self.onChange(newValue);
            	}
            	if (!newValue || (self.noNumberUnit || {})[newValue]){
            		self.numberField.set('value', '');
            		//self.numberField.set('disabled', true);
            		dst.set(self.numberField.domNode, 'display', 'none');
            	}else{
            		self.numberField.set('disabled', false);
            		dst.set(self.numberField.domNode, 'display', '');
            	}
            });
            this.domNode.appendChild(this.unitField.domNode);   
        },
        focus: function(){
            this.numberField.domNode.style.display === 'none' ? this.unitField.focus() : this.numberField.focus();
        },
        setStyleToChanged: function(){
            this.numberField.set('style', {backgroundColor: wutils.changeColor});
            this.unitField.set('style', {backgroundColor: wutils.changeColor});
        },
        setStyleToUnchanged: function(){
            this.numberField.set('style', {backgroundColor: ''});
            this.unitField.set('style', {backgroundColor: ''});
        }, 
        _setValueAttr: function(value){
            this._set("value", value);
            if (typeof this.value == 'string' && this.value != ''){
                var values = this.concat ? value.match(/(\d*)(.*)/).splice(1, 2) : JSON.parse(this.value);
                if (this.numberField){
                    this.numberField.set('value', values[0], arguments[1]);
                }
                if (this.unitField){
                    this.unitField.set('value', values[1], arguments[1]);
                }
            }else{
                this.numberField.set('value', '', arguments[1]);
                this.unitField.set('value', '', arguments[1]);
            }
        },
        _getValueAttr: function(){
            var numberValue = this.numberField.get('value'), unitValue = this.unitField.get('value');
			return this.concat ? numberValue + unitValue : ((numberValue == '' && unitValue == '') ? '' : JSON.stringify([numberValue, unitValue]));
        },
		_getDisplayedValueAttr: function(){
			var unitValue = this.unitField.get('value');
			
			return this.numberField.get('value') + ' ' + (unitValue ? utils.findReplace(this.unitField.store.data, 'id', unitValue, 'name', this.storeCache || (this.storeCache = {})) : unitValue);
		},
        _setDisabledAttr: function(value){
            this.inherited(arguments);
            this.numberField.set('disabled', value);
            this.unitField.set('disabled', value);
        }
    });
}); 
