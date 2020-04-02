define (["dojo/_base/declare", "dijit/form/MultiSelect", "dijit/Tooltip", "tukos/utils"], 
    function(declare, MultiSelect, Tooltip, utils){
    return declare([MultiSelect], {
        postCreate: function(){
            var valueToRestore = this.value;
        	this.inherited(arguments);
            for (var  i in this.options){
                var opt = dojo.doc.createElement('option'), option = this.options[i];
                if (typeof option === 'string'){
                	opt.innerHTML = option;
                }else{
                	opt.innerHTML = option.option;
                	var tooltip = new Tooltip({connectId: [opt], label: option.tooltip});
                }
                opt.value = i;
                this.domNode.appendChild(opt);
            }
            this.set('value', valueToRestore);
        },
        _getServerValueAttr: function(){
			return JSON.stringify(this.get('value'));;
		},
		_getDisplayedValueAttr: function(){
			var values = this.get('value'), options = this.options, displayedValue = [];
			values.forEach(function(value){
				var option = options[value];
				displayedValue.push(option.option || option);
			});
			return displayedValue;
		},
        _setValueAttr: function(value){
			if (value && typeof value === 'string'){
				value = JSON.parse(value);
			}
        	this.inherited(arguments);
		},
		getOptions: function(){
			var result = {};
			utils.forEach(this.options, function(option, key){
				result[key] = option.option || option;
			});
			return result;
		}
    }); 
});
