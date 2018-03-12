define(["dojo/_base/declare", "dojo/dom-construct", "dijit/_WidgetBase", "dijit/form/NumberTextBox", "tukos/StoreSelect", "dojo/json", "dojo/domReady!"], 
function(declare, dct, Widget, NumberTextBox, StoreSelect, JSON){
    return declare("tukos.UnitSelect", Widget, {
        postCreate: function(){
            numberField = new NumberTextBox(this.number, dojo.doc.createElement('div'));
            this.domNode.appendChild(numberField.domNode); 
            this.numberField = numberField;
            unitField = new StoreSelect(this.unit, dojo.doc.createElement('div')); 
            this.domNode.appendChild(unitField.domNode);   
            this.unitField = unitField;   
        },
        _setValueAttr: function(value){
            this._set("value", value);
            var values = JSON.parse(this.value);
            this.numberField.set('value', values.value);
            this.unitField.set('value', values.unit);
            var none = 'none';
        },
        _getValueAttr: function(){
            this.get("value", value);
            var values = JSON.parse(this.value);
            this.numberField.set('value', values.value);
            this.unitField.set('value', values.unit);
            result = {value: this.number.get('value'), unit: this.unit.get('value')};
            resultString = JSON.stringify(result);
            return resultString;
        }
    });
}); 

