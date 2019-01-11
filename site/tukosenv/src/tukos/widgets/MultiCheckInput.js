define (["dojo/_base/declare", "dijit/_WidgetBase", "dijit/registry", "dojo/query", "tukos/utils"], 
    function(declare, Widget, registry, query, utils){
    return declare([Widget], {

        _getValueAttr: function(){
            var selectedInput = query('input:checked', this.domNode);
            if (this.uniquechoice){
                return selectedInput.length ? selectedInput[0].value : '';           	
            }else{
            	var values = [];
            	selectedInput.forEach(function(input){
            		values.push(input.value);
            	});
            	return values;
            }
        },
        _setValueAttr: function(value){
        	var inputs = query('input', this.domNode), values = this.uniquechoice ? [value] : value;
        	inputs.forEach(function(input){
        		var widget = registry.getEnclosingWidget(input), checkedValue = utils.in_array(input.value, values) ? true : false;
        		if (widget){
        			widget.set('checked', checkedValue);
        		}else{
        			input.checked = checkedValue;
        		}
        	});
        }
    }); 
});
