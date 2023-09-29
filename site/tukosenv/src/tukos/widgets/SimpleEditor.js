define (["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "dijit/Editor", "dojoFixes/dijit/_editor/plugins/FullScreen", "dojox/editor/plugins/StatusBar", "dojox/editor/plugins/FindReplace", "tukos/widgets/editor/plugins/Smiley", "tukos/utils", "tukos/hiutils"], 
    function(declare, lang, when, Editor, FullScreen, StatusBar, FindReplace, Smiley, utils, hiutils){
	return declare([Editor], {

    	constructor: function(args){
            args.plugins = ['undo', 'redo','|','bold','italic','underline','strikethrough','subscript','superscript','removeFormat','|', 'insertOrderedList', 'insertUnorderedList', 'indent', 'outdent',
                            'justifyLeft', 'justifyCenter', 'justifyRight','justifyFull', 'insertHorizontalRule'];
            args.extraPlugins = args.extraPlugins || ['fullScreen', 'statusBar', 'FindReplace', 'Smiley'];
            args.styleSheets = require.toUrl('dijit/themes/claro/claro.css');
        },
        changeStyle: function(property, value){
            this.document.body.style[property] = value;
        },
		setValue: function(value){
			if(!this.isLoaded){
				// try again after the editor is finished loading
				this.onLoadDeferred.then(lang.hitch(this, function(){
					this.setValue(value);
				}));
				return;
			}
            if (hiutils.hasTranslation(value)){
                this.serverValue = value;
                var _arguments = arguments;
                when(hiutils.translateParams(value, this), lang.hitch(this, function(newValue){
                    value = newValue;
                    this.inherited(_arguments);
                }));
            }else{
                if (!hiutils.hasUntranslation(value)){
                    delete this.serverValue;
                    delete this.serverValueDeferred;//serverValueDeferred may be created via tukos/widgets/Editor
                }
                this.inherited(arguments);
            }
        },

        _getValueAttr: function(){
               var value = this.inherited(arguments), forceSpace = '';//this.isInViewSource && this.isInViewSource() ? '' : '&nbsp;';
               return value ? forceSpace + value.replace(/<span><\/span>|colspan="1"|rowspan="1"/g, '')/*.replace(/[\n\t ]+/g, ' ')*/.trim() + forceSpace : value;
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
            	this._plugins.forEach(function(plugin){//JCH so that the toolbar does not disappear below the browser toolbar when the editor is in full screen mode, and triggered from a TooltipDialog
            		if (plugin.button){
            			plugin.button.scrollOnFocus = false;
	            	}
            	});
            }));
        },
        onLoad: function(){
        	this.inherited(arguments);
            this.onLoadDeferred.then(lang.hitch(this, function(){
            	this.editNode.className = 'claro';
            }));
        },
    }); 
});
