define (["dojo/_base/declare", "dijit/_WidgetBase", "dojo/query"], 
    function(declare, Widget, query){
    return declare([Widget], {

        _getValueAttr: function(){
            var selectedRadio = query('input:checked', this.domNode);
            return selectedRadio.length ? selectedRadio[0].value : '';
        },
        _setValueAttr: function(value){
        	var selectedRadio = query('input:checked', this.domNode), radioToSelect;
        	if (value && (radioToSelect = query('input[value="' + value + '"]', this.domNode)).length){
            		if (selectedRadio.length && radioToSelect[0] === selectedRadio[0]){
            			return;
        			}else{
        				selectedRadio[0].checked = false;
        				radioToSelect[0].checked = true;
        			}
        	}else if (selectedRadio.length){
        		selectedRadio[0].checked = false;
        	}
        }
    }); 
});
