/*
 *  loads plugins required by tukos & a few enhancements
 */
define (["dojo/_base/declare", "dojo/_base/array", "dojo/_base/connect", "dojo/_base/lang", "dojo/_base/Deferred", "dojo/dom", "dojo/dom-style", "dojo/mouse", "dojo/when", "dijit/Editor",  "dijit/_editor/RichText",
         "tukos/widgets/editor/ShortCutKeys", "tukos/PageManager",
         "dijit/_editor/plugins/FontChoice", "dijit/_editor/plugins/TextColor", "tukos/widgets/editor/plugins/LinkDialog",  "dojoFixes/dijit/_editor/plugins/FullScreen",
         "dijit/_editor/plugins/Print"/*, "dojoFixes/dijit/_editor/plugins/AlwaysShowToolbar"*/, "dojoFixes/dijit/_editor/plugins/ViewSource",
         "dojox/editor/plugins/StatusBar", "dojox/editor/plugins/FindReplace", 
         "dojoFixes/dojox/editor/plugins/TablePlugins", "tukos/utils", "tukos/hiutils", "tukos/menuUtils", "tukos/widgets/editor/plugins/TukosLinkDialog",
         "tukos/widgets/editor/plugins/TemplateProcess","tukos/widgets/editor/plugins/Inserter","tukos/widgets/editor/plugins/SelectionEditor","tukos/widgets/editor/plugins/FitImage"/*, "dojoFixes/dojox/editor/plugins/ResizeTableColumn"*/, "dojo/domReady!"], 
    function(declare, array, connect, lang, Deferred, dom, domStyle, mouse, when, Editor, RichText, ShortCutKeys, PageManager, FontChoice, TextColor, LinkDialog, FullScreen, print/*, AlwaysShowToolbar*/, ViewSource, StatusBar, FindReplace,
    		 TablePlugins, utils, hiutils, mutils, TukosLinkDialog, TemplateProcess, Inserter, SelectionEditor, FitImage){
    return declare([Editor, ShortCutKeys], {

    	constructor: function(args){
            args.plugins = ['undo', 'redo'/*, 'cut','copy','paste'*/,'|','bold','italic','underline','strikethrough','subscript','superscript','removeFormat','|', 'insertOrderedList', 'insertUnorderedList', 'indent', 'outdent',
                            'justifyLeft', 'justifyCenter', 'justifyRight','justifyFull', 'insertHorizontalRule'];
            args.extraPlugins = args.extraPlugins ||  
                ['fontName', 'fontSize', 'formatBlock', 'foreColor', 'hiliteColor', 'createLink', 'unlink', 'insertImage', 'fullScreen', {name: 'viewSource', stripScripts: false}, 'TukosLinkDialog'/*, 'ChoiceList', 'TemplateProcess'*/, 
                 /*'alwaysShowToolbar', */'statusBar', 'insertTable', 'modifyTable'/*,   'resizeTableColumn'*/, 'modifyTableSelection', 'Inserter', 'print', 'FindReplace', 'SelectionEditor', 'FitImage'];
            if (args.optionalPlugins){
                args.extraPlugins = args.extraPlugins.concat(args.optionalPlugins);
            }
        },

        changeStyle: function(property, value){
            this.document.body.style[property] = value;
        },

        postCreate: function(){
            this.inherited(arguments);
            this.contentDomPostFilters = [];//jch: don't eliminate empty nodes at end, makes life of the end-user more difficult
            this.watch('value',  lang.hitch(this, function(name, oldValue, newValue){
                if (hiutils.hasUntranslation(newValue)){
                    //console.log('watch - has untranslation old: ' + oldValue);
                    //console.log('newValue: ' + newValue);
                    this.serverValueDeferred = new Deferred();
                    when(hiutils.untranslateParams(newValue, this), lang.hitch(this, function(newValue){
                        //console.log('watch - untranslatioin completed - newValue: ' + newValue);
                    	this.serverValue = newValue;
                        this.serverValueDeferred.resolve(newValue);
                    }));
                }else if (hiutils.hasTranslation(newValue)){
                    //console.log('watch - has translation and no unstranslation: ' + newValue);
                    this.serverValue = newValue;
                    delete this.serverValueDeferred;
                }else{
                    //console.log('watch - deleting serverValue for: ' + this.id);
                    delete this.serverValue;
                    delete this.serverValueDeferred;
                }
            }));
            this.addShortCutKeys();
        },

        setValue: function(value){
			if(!this.isLoaded){
				// try again after the editor is finished loading
				this.onLoadDeferred.then(lang.hitch(this, function(){
					this.setValue(value);
				}));
				return;
			}
            //console.log('setValue - id: ' + this.id + ' value: ' + value); //console.log('setValue - serverValue: ' + this.serverValue);
            if (hiutils.hasTranslation(value)){
                this.serverValue = value;
                var _arguments = arguments;
                //console.log('setValue - has translations');
                when(hiutils.translateParams(value, this), lang.hitch(this, function(newValue){
                    value = newValue;
                    //console.log('setValue: before inherited - value: ' + value);
                    this.inherited(_arguments);
                    //console.log('setValue l 57:editor setter was called for ' + this.id);
                }));
            }else{
                if (!hiutils.hasUntranslation(value)){
                    //console.log('setValue l 60 - deleting serverValue for: ' + this.id);
                    delete this.serverValue;
                    delete this.serverValueDeferred;
                }
                this.inherited(arguments);
            }
            //console.log('leaving setValue - ' + value);
        },

        _getValueAttr: function(){
               var value = this.inherited(arguments);
               return value ? value.replace(/<span><\/span>|colspan="1"|rowspan="1"|id="tdid\d+"/g, '').replace(/[\n\t ]+/g, ' ') : value;
        },

        _getServerValueAttr: function(){
            var deferred = this.serverValueDeferred;
            return (deferred ? (deferred.isResolved() ? this.serverValue : deferred) : this.serverValue);
        },
        
        startup: function(){
            //var self = this;
        	if (this.style && typeof this.style === 'object'){// richtext only supports string notation
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
            this.statusBar.resizeHandle.on ('resize', lang.hitch(this, function(evt){
                var newHeight = this.height = domStyle.get(this.editingArea, 'height') + "px";
            	lang.setObject((this.itemCustomization || 'customization') + '.widgetsDescription.' + this.widgetName + '.atts.height', newHeight, this.pane);
                //this.layoutHandle.resize();
            }));
        },

        destroy: function(){
			array.forEach(this._plugins, function(p){
				if(p && p.destroy){
					p.destroy();
				}
			});
			this._plugins = [];
			if (this.toolbar){
                this.toolbar.destroyRecursive();
                delete this.toolbar;
            }else{
            }
            RichText.prototype.destroy.apply(this, arguments);
        },

        pasteHtmlAtCaret: function(html, selectPastedContent) {// found here: http://stackoverflow.com/questions/6690752/insert-html-at-caret-in-a-contenteditable-div/6691294#6691294
            var sel = dijit.range.getSelection(this.window), range = sel.getRangeAt(0);
            range.deleteContents();
            var el = document.createElement("div");
            el.innerHTML = html;
            var frag = document.createDocumentFragment(), node, lastNode;
            while ( (node = el.firstChild) ) {
                lastNode = frag.appendChild(node);
            }
            var firstNode = frag.firstChild;
            range.insertNode(frag);

            // Preserve the selection
            if (lastNode) {
                range = range.cloneRange();
                range.setStartAfter(lastNode);
                if (selectPastedContent) {
                    range.setStartBefore(firstNode);
                } else {
                    range.collapse(true);
                }
                sel.removeAllRanges();
                sel.addRange(range);
            }
        }
    }); 
});
