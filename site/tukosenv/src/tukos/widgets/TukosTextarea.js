define(["dojo/_base/declare", "dijit/form/SimpleTextarea", "tukos/utils"], 
    function(declare, TextArea, utils){
    return declare([TextArea], {

        autoExpand: function(node){
        	var value = this.get('value');
        	if (value.length*10 < node.clientWidth && value.indexOf('\n') === -1){
        		node.style.height = '15px';
        	}else{
            	node.style.height = 'inherit';
            	var computed = window.getComputedStyle(node);
            	var height = parseInt(computed.getPropertyValue('border-top-width'), 10)
            	             + parseInt(computed.getPropertyValue('padding-top'), 10)
            	             + node.scrollHeight
            	             + parseInt(computed.getPropertyValue('padding-bottom'), 10)
            	             + parseInt(computed.getPropertyValue('border-bottom-width'), 10);
            	node.style.height = height + 'px';        		
        	}
        },
		_onInput: function(/*Event?*/ e){
			this.inherited(arguments);
			this.autoExpand(e.target);
		},
        focus: function(){
        	this.inherited(arguments);
        	if (!this.disabled){
        		this.autoExpand(this.domNode);
        	}
        },
        _setValueAttr: function _setValueAttr (value){
			if (this.translations){
				utils.forEach(this.translations, function(translated, untranslated){
					if (value.includes(untranslated)){
						value = value.replaceAll(untranslated, translated);
					}
				});
			}
			this.inherited(_setValueAttr, arguments);
		},
        _getServerValueAttr: function(){
			let value = this.get('value');
			if (value && this.translations){
				utils.forEach(this.translations, function(translated, untranslated){
					if (value.includes(translated)){
						value = value.replaceAll(translated, untranslated);
					}
				});
			}
			return value;
		}
    });
});
