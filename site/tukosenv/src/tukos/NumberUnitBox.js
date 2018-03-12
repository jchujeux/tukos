define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-style", "dijit/_WidgetBase", "dijit/_FocusMixin", "dijit/form/NumberTextBox", "tukos/StoreSelect", "tukos/_WidgetsMixin", "tukos/widgetUtils", "dijit/registry", "dojo/json"], 
function(declare, lang, domstyle, Widget, _FocusMixin, NumberTextBox, StoreSelect, _WidgetsMixin, wutils, registry, JSON){
    return declare([Widget, _FocusMixin, _WidgetsMixin], {
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            this.numberField = new NumberTextBox(this.number, dojo.doc.createElement('div'));
            this.numberField.watch('value', lang.hitch(wutils, wutils.subWidgetWatchCallback, this, this.numberField));
            this.domNode.appendChild(this.numberField.domNode); 
            this.unitField = new StoreSelect(this.unit, dojo.doc.createElement('div')); 
            this.unitField.watch('value', lang.hitch(wutils, wutils.subWidgetWatchCallback, this, this.unitField));
            this.unitField.on('change', function(newValue){
            	if (self.onChange){
            		self.onChange(newValue);
            	}
            });
            this.domNode.appendChild(this.unitField.domNode);   
        },

        focus: function(){
            this.numberField.focus();
        },

        setStyleToChanged: function(widget){
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
                var values = JSON.parse(this.value);
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
            return JSON.stringify([this.numberField.get('value'), this.unitField.get('value')]);
        },
        _setDisabledAttr: function(value){
            this.inherited(arguments);
            this.numberField.set('disabled', value);
            this.unitField.set('disabled', value);
        }
    });
}); 
