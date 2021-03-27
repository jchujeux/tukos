define(["dojo/_base/declare", "dojox/mobile/TextArea"], 
  function(declare, TextArea){
    var minHeight = 30, minHeightString = minHeight + 'px';;
	return declare([TextArea], {

    	constructor: function(args){
    		var style = args.style || {};
    		style.height = style.height || minHeightString;
    		style.width = parseInt(style.width || '20') > 20 ? '100%' : style.width;
    		args.style = style;
    	},

    	autoExpand: function(node){
        	var value = this.get('value'), scrollHeight = node.scrollHeight;
        	if (value.length*8 < node.style.width && value.indexOf('\n') === -1){
        		node.style.height = minHeightString;
        	}else{
            	node.style.height = Math.max(minHeight, node.scrollHeight) + 'px';
        	}
        },

		_setValueAttr: function(value){
			this.domNode.style.height = minHeightString;
			this.inherited(arguments);
			this.autoExpand(this.domNode);			
		},
        _onInput: function(e){
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