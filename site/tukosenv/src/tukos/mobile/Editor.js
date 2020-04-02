define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-style", "dijit/Editor", "dijit/_editor/plugins/FullScreen", "dojox/editor/plugins/StatusBar", "tukos/utils"], 
    function(declare, lang, dst, Editor, FullScreen, StatusBar, utils){
    return declare([Editor], {
    	
    	constructor: function(args){
    		args.extraPlugins = ['fullScreen', 'statusBar'];
    		//args.height = "250px";
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
        changeStyle: function(property, value){
            this.document.body.style[property] = value;
        },
        startup: function(){
        	if (utils.isObject(this.style)){// richtext only supports string notation
                var style = this.style, changeStyle = lang.hitch(this, this.changeStyle);
                delete this.style;
            }
            this.inherited(arguments);
            this.onLoadDeferred.then(lang.hitch(this, function(){
            	if (style){
                    utils.forEach(style, function(value, property){
                        changeStyle(property, value);
                    });
            	}
            }));
        	this.toolbar.set('style', {display: 'none'});
            this.statusBar.resizeHandle.on ('resize', lang.hitch(this, function(evt){
                var newHeight = this.height = dst.get(this.editingArea, 'height') + "px";
            	lang.setObject((this.itemCustomization || 'customization') + '.widgetsDescription.' + this.widgetName + '.atts.height', newHeight, this.pane);
            }));
        }
    });
});
