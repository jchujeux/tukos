define(["dojo/_base/declare", "dijit/Editor", "dijit/_editor/plugins/FullScreen"], 
    function(declare, Editor, FullScreen){
    return declare([Editor], {
    	
    	constructor: function(args){
    		args.extraPlugins = ['fullScreen'];
    		args.height = "250px";
    	},

    	postCreate: function(){
			this.inherited(arguments);
			var self = this;
        	this.on('click', function(){
        		this.toolbar.set('style', {display: "block"});
        	});
			this.on('blur', function(){
				this.toolbar.set('style', {display: "none"});
			});	
        },
        startup: function(){
        	this.inherited(arguments);
        	this.toolbar.set('style', {display: 'none'});
        }

    });
});
