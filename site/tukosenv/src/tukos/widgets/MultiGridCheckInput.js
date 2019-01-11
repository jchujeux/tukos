define (["dojo/_base/declare", "dijit/_WidgetBase", "dijit/registry", "dojo/query", "tukos/utils"], 
    function(declare, Widget, registry, query, utils){
    return declare([Widget], {

        _getValueAttr: function(){
            var selectedInputs = query('input:checked', this.domNode), values = {}, uniquechoice = this.uniquechoice;
            selectedInputs.forEach(function(input){
            	var topic = input.name, value = input.value;
            	values[topic] = uniquechoice ? value : (values[topic] || []).concat(value);
            });
            return values;
        },
        _setValueAttr: function(values){
        	var inputs = query('input', this.domNode), uniquechoice = this.uniquechoice;
        	inputs.forEach(function(input){
        		var widget = registry.getEnclosingWidget(input), checkedValue = uniquechoice ? input.value === values[input.name] : (utils.in_array(input.value, values[input.name] || []) ? true : false);
        		if (widget){
        			widget.set('checked', checkedValue);
        		}else{
        			input.checked = checkedValue;
        		}
        	});
        }
    }); 
});
