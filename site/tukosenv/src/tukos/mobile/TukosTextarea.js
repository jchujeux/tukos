define(["dojo/_base/declare", "dojox/mobile/TextArea"], 
    function(declare, TextArea){
    return declare([TextArea], {
    	constructor: function(args){
    		var style = args.style || {};
    		style.height = style.height || '25px';
    		style.width = parseInt(style.width || '20') > 20 ? '20em' : style.width;
    		args.style = style;
    	},
    	autoExpand: function(node){
        	var value = this.get('value'), scrollHeight = node.scrollHeight;
        	if (value.length*8 < node.style.width && value.indexOf('\n') === -1){
        		node.style.height = '25px';
        	}else{
            	node.style.height = Math.max(25, node.scrollHeight) + 'px';
        	}
        },
		_setValueAttr: function(value){
			this.domNode.style.height = '25px';
			this.inherited(arguments);
			this.autoExpand(this.domNode);			
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
        }
    });
});
