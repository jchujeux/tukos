define (["dojo/_base/declare", "dijit/form/MultiSelect", "dijit/Tooltip", "tukos/utils", "dojo/query!css2", "dojo/domReady!"], 
    function(declare, MultiSelect, Tooltip, utils){
    return declare([MultiSelect], {
        postCreate: function(){
            var valueToRestore = this.value;
        	this.inherited(arguments);
            this.set('value', valueToRestore);
        },
        _getServerValueAttr: function(){
			return JSON.stringify(this.get('value'));
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
		_setOptionsAttr: function(options){
            this.inherited(arguments);
			this.domNode.innerHTML = '';
			for (var  i in options){
                var opt = dojo.doc.createElement('option'), option = options[i];
                if (typeof option === 'string'){
                	opt.innerHTML = option;
                }else{
                	opt.innerHTML = option.option;
					if (!this.tooltip){
						this.createTooltip();
					}
                	opt.tooltipText = option.tooltip;
                }
                opt.value = i;
                this.domNode.appendChild(opt);
            }
		},
		getOptions: function(){
			var result = {};
			utils.forEach(this.options, function(option, key){
				result[key] = option.option || option;
			});
			return result;
		},
		createTooltip: function(){
			this.tooltip = new Tooltip({connectId: [this.domNode], selector: 'option', getContent: function(matchedNode){
				return matchedNode.tooltipText;
			}});
		}
    }); 
});
